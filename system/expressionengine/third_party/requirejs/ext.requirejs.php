<?php 


class Requirejs_ext {
    var $EE;

    var $name           = 'RequireJS for EE';
    var $version        = '1.0';
    var $description    = 'Loads RequireJS in the CP for use by other addons.';
    var $settings_exist = 'n';
    var $docs_url       = '';

    var $settings       = array();



    /**
     * Constructor
     *
     * @param   mixed   Settings array or empty string if none exist.
     */
    function __construct($settings = '')
    {
        $this->EE =& get_instance();

        $this->settings = $settings;
    }



    /**
     * Attatch RequireJS API to EE global object
     * 
     * Using CI Hooks is the only way that we can reliably ensure that our load_js method is 
     * the LAST, since extensions, modules, fieltypes and accessories can all potentially add
     * to the RequireJS queue
     * 
     * @param  object   $Session      Session instance
     * @return null          
     */
    public function _requirejs_init($Session)
    {
        global $EXT;


        //If this request is a JS or AJAX call, dont bother to load require.  We want CP page loads only
        if($this->EE->input->get("C") == "javascript"){
            return;
        }

        //Enable CI Hooks
        $EXT->enabled = TRUE;

        //Create the post_controller hook array if needed
        if(!isset($EXT->hooks['post_controller'])){
            $EXT->hooks['post_controller'] = array();
        }

        //Add our hook
        $EXT->hooks['post_controller'][] = array(
            'class'    => 'Requirejs_ext',
            'function' => 'load_js',
            'filename' => 'ext.requirejs.php',
            'filepath' => "third_party/requirejs/",
            'params'   => array()
        );

        //Attach the RequireJS model to the singleton to act as the API
        $this->EE->load->model('requirejs', null, "requirejs");   
    }





    public function load_js()
    {
        $scripts = Requirejs::queue();


        $str = "";
        foreach ($scripts as $script) {
            if(!is_array($script['deps'])) {
                $str .= "'".$script['deps']."', ";
            } else {
                foreach ($script['deps'] as $scrpt) {
                    $str .= "'$scrpt', ";
                }
            }
        }

        if(strlen($str) > 0){
            $str = substr($str, 0, strlen($str)-2);
        }
        
        //Include RequireJS Script and configure
        $js = "
<script src='".URL_THIRD_THEMES."requirejs/javascript/require.js' type='text/javascript'></script>
<script type=\"text/javascript\">
    require.config({
        baseUrl: '".base_url()."',
        paths: {
            'themes' : '".URL_THIRD_THEMES."../'
        }
    });

    //JS Scripts
    require([$str], function() {
";

    //Script Callbacks
    foreach ($scripts as $script) {
        if($script['callback']){
            if($script['deps']){
                $js .= "\n\n //Callback for script: ".$script['deps']."\n";    
            } else {
                foreach ($script['deps'] as $scrpt) {
                    $js .= "\n\n //Callback for scripts: \n";
                    $js .= "   - $scrpt \n";
                }
                
            }
            $js .= "\n";
            $js .= $script['callback'];
            $js .= "\n";
            
        }
    }


        $js .= "
    });

</script>
";

        $this->EE->output->final_output = str_replace("</head>", $js."\n\n</head>", $this->EE->output->final_output);
    }






    /**
     * Activate, Update and Delete
     */

    function activate_extension()
    {
        $this->settings = array();


        $data = array(
            'class'     => __CLASS__,
            'method'    => '_requirejs_init',
            'hook'      => 'sessions_start',
            'settings'  => serialize($this->settings),
            'priority'  => 1,
            'version'   => $this->version,
            'enabled'   => 'y'
        );

        $this->EE->db->insert('extensions', $data);
    }




    function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }

        if ($current < '1.0')
        {
            // Update to version 1.0
        }

        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->update(
                    'extensions',
                    array('version' => $this->version)
        );
    }


    function disable_extension()
    {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }
}
// END CLASS