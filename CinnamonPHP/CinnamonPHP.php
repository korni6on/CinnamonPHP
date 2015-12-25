<?php

/**
 * Description of CinnamonPHP
 *
 * @author Ivelin Kostov
 */
class CinnamonPHP {

    protected $templatePaths;
    protected $cacheDir;
    protected $externalCacheDir;
    protected $forceRegenerateCache;
    private $cacheSufix;
    
    /**
     * Construct CinnamonPHP
     * @return object Class constructor
     */

    public function __construct() {
        $this->templatePaths = array();
        $this->cacheDir = '';
        $this->externalCacheDir = false;
        $this->forceRegenerateCache = false;

        $this->cacheSufix = 'CinnamonPHP.inc';
    }
    
    /**
     * Process template.
     * @param string Absolute or relative path to template 
     * @param bool If TRUE will remove double space, new lines and space between tags else will return code as it is writen in template.
     * @return string Processed string from template.
     * @throws Exception Throw exception if cannot include cache file
     */
    public function LoadTemplate($templateName, $compress = FALSE) {

        $templateRealPath = '';
        $templateCacheRealPath = '';

        foreach ($this->templatePaths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $templateName)) {
                $templateRealPath = $path . DIRECTORY_SEPARATOR . $templateName;
                if ($this->externalCacheDir) {
                    $templateName = str_replace(array('/', '\\'), array('__', '__'), $templateName);
                    $templateCacheRealPath = $this->cacheDir . DIRECTORY_SEPARATOR . $templateName . $this->cacheSufix;
                } else {
                    $templateCacheRealPath = $path . DIRECTORY_SEPARATOR . $templateName . $this->cacheSufix;
                }
                break;
            }
        }

        if (!file_exists($templateCacheRealPath) || filemtime($templateRealPath) > filemtime($templateCacheRealPath) || $this->forceRegenerateCache) {
            $template = $this->GenerateCacheString(file_get_contents($templateRealPath));
            file_put_contents($templateCacheRealPath, $template);
        }

        try {
            include $templateCacheRealPath;
        } catch (Exception $ex) {
            throw new Exception("Canot include cache file");
        }

        $code = ob_get_clean();

        if ($compress) {
            $search = array(
                '/\>[^\S ]+/s', // strip whitespaces after tags, except space
                '/[^\S ]+\</s', // strip whitespaces before tags, except space
                '/(\s)+/s'       // shorten multiple whitespace sequences
            );
            $replace = array(
                '>',
                '<',
                '\\1'
            );
            $code = preg_replace($search, $replace, $code);
            $code = preg_replace('~>\\s+<~m', '><', $code); //
        }

        return $code;
    }
    
    public function ForceRegenerateCache($force) {
        return $this->forceRegenerateCache = $force ? TRUE : FALSE;
    }

    public function SaveInCacheDir($save) {
        return $this->externalCacheDir = $save ? TRUE : FALSE;
    }

    public function SetCacheDire($path, $forceCreate = FALSE) {
        $realPath = realpath($path);
        if (file_exists($path) && is_dir($path) && $realPath !== FALSE) {
            $this->externalCacheDir = TRUE;
            $this->cacheDir = $realPath;
            return TRUE;
        } elseif ($forceCreate === TRUE) {
            if (mkdir($path, 0777, TRUE) === TRUE) {
                return $this->SetCacheDire($path);
            }
        }
        return FALSE;
    }

    public function RemoveCacheDir() {
        $this->externalCacheDir = false;
        $this->cacheDir = '';
        return TRUE;
    }

    public function GetExternalCacheDir() {
        return $this->externalCacheDir;
    }

    public function AddTemplatePath($path) {
        $path = realpath($path);
        if (file_exists($path) && is_dir($path) && $path !== FALSE) {
            $this->templatePaths[] = $path;
            return TRUE;
        }
        return FALSE;
    }

    public function RemoveTemplatePath($path) {
        $path = realpath($path);
        if (($key = array_search($path, $this->$templatePaths) && $path !== FALSE) !== FALSE) {
            unset($this->templatePaths[$key]);
            return TRUE;
        }
        return FALSE;
    }

    public function GetTemplatePaths() {
        return $this->templatePaths;
    }

    protected function GenerateCacheString($templateContent) {
        $matches = array();
        $globalVariables = array();
        preg_match_all('/[^\\\\]({{([\\sa-z0-9\\.\\(\\)\\|\\,]+)}})/mi', $templateContent, $matches);
        $code = "<?php\r\n";
        foreach ($matches[1] as $key => $value) {
            $var = trim($matches[2][$key]);
            if (!in_array($var, $globalVariables)) {
                $code.='global $' . $var . ";\r\n";
                $globalVariables[] = $var;
            }
            $templateContent = preg_filter('/(?<!\\\\)(' . $value . ')/', '<?php echo isset($' . $var . ') ? $' . $var . ' : ""; ?>', $templateContent, 1);
        }
        $code .= "ob_start();\r\n?>\r\n";
        $code.= $templateContent;
        return $code;
    }

}
