<?php 


class Requirejs_ext {
    var $EE;

    var $name           = 'RequireJS for EE';
    var $version        = '1.2';
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
     * Using CI Hooks is the only way that we can reliably ensure that our _requirejs_init method is 
     * called LAST since extensions, modules, fieltypes and accessories can all potentially add
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

        //Load our modified core Hooks class
        $this->EE->load->file(APPPATH . "third_party/requirejs/core/RJS_Hooks.php");

        //We overwrite the CI_Hooks class with our own since the CI_Hooks class will always load
        //hooks class files relative to APPPATH, when what we really need is to load RequireJS hook from the
        //themes folder, which we KNOW can always be found with PATH_THIRD. Hence we extend the class and
        //simply redefine the _run_hook method to load relative to PATH_THIRD. Simples.
        $RJS_EXT = new RJS_Hooks();

        //Capture existing hooks just in case (although this is EE - it's unlikely)
        $RJS_EXT->hooks = $EXT->hooks;

        //Enable CI Hooks
        $RJS_EXT->enabled = TRUE;

        //Create the post_controller hook array if needed
        if(!isset($RJS_EXT->hooks['post_controller'])){
            $RJS_EXT->hooks['post_controller'] = array();
        }

        //Add our hook
        $RJS_EXT->hooks['post_controller'][] = array(
            'class'    => 'Requirejs_ext',
            'function' => 'load_js',
            'filename' => 'ext.requirejs.php',
            'filepath' => "third_party/requirejs" ,
            'params'   => array()
        );

        //Overwrite the global CI_Hooks instance with our modified version
        $EXT = $RJS_EXT;

        //Attach the RequireJS model to the singleton to act as the API
        $this->EE->load->model('requirejs', null, "requirejs");
    }





    public function load_js()
    {
        $scripts = Requirejs::queue();
        $shims = Requirejs::shimQueue();

        //No scripts to load? Bailout.
        if(count($scripts) == 0 && count($shims) == 0) return;

        //Load the RequireJS script early
        $js1 = "<script src='".URL_THIRD_THEMES."requirejs/javascript/require.js' type='text/javascript'></script>";


        $scripts = Requirejs::queue();
        $shims = Requirejs::shimQueue();


        $str = "";
        foreach ($scripts as $script) {
   
            foreach ($script['deps'] as $scrpt) {
                $str .= $this->_formatUrl($scrpt).", ";
            }
        
        }

        if(strlen($str) > 0){
            $str = substr($str, 0, strlen($str)-2);
        }
        
        //Include configure RequireJS and concatenate callbacks
        $js2 = "
<script type=\"text/javascript\">
    require.config({
        baseUrl: '".URL_THIRD_THEMES."../',
        paths: {
            'URL_THIRD_THEMES'  : '".URL_THIRD_THEMES."',
            'css'               : '".URL_THIRD_THEMES."requirejs/javascript/plugins/css/css.min',
            'text'              : '".URL_THIRD_THEMES."requirejs/javascript/plugins/text/text',
            'jquery'            : '".URL_THIRD_THEMES."../javascript/compressed/jquery/jquery'
        },
        shim: {
            ";
            
        foreach ($shims as $shim) {
            $js2 .= "'".$shim['script']."' : ['".implode("', '", $shim['deps'])."'],";
        }
        $js2 = substr($js2, 0, strlen($js2)-1);


        $js2 .= "
        }
    });

    //JS Scripts
    require([$str], function() {
";

    //Script Callbacks
    foreach ($scripts as $script) {
        if($script['callback']){
            $js2 .= "\n\n // Callback for script: \n";
            foreach ($script['deps'] as $scrpt) {
                $js2 .= " // - $scrpt \n";
            }
            
        
            $js2 .= "\n";
            $js2 .= $script['callback'];
            $js2 .= "\n";
            
        }
    }


        $js2 .= "
    });

</script>
";

        $this->EE->output->final_output = str_replace(array("</title>", "</head>"), array("</title>\n\n".$js1."\n", $js2."\n\n</head>"), $this->EE->output->final_output);
    }



    /**
     * Script URL Formatting
     * 
     * If requireJS loads a regular script.js file, then it ignore the baseUrl.  RequireJS dictates that we
     * should use the require.toUrl() method to convert a regular .js script address to on relative to baseUrl.
     * For convenience in the PHP API, lets hide this need from the user.
     * 
     * @param  string $fullUrl  URL to the script (or CSS, HTML etc)
     * @return string           Modified script address
     */
    public function _formatUrl($fullUrl='')
    {
        $parts = explode("!", $fullUrl);
        $url = $fullUrl;
        $plugin = "'";

        if(count($parts) > 1){
            $url = $parts[1];
            $plugin = "'".$parts[0]."!";
        }
        
        if(substr($url, -3) == ".js" || substr($url, 0, 4) == "http" || substr($url, 0, 1) == "/") {
            $url = "' + require.toUrl('".$url."')";
        } else {
            $url = $url."'";
        }

        
        return $plugin.$url;
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