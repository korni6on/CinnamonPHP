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

    public function __construct() {
        $this->templatePaths = array();
        $this->cacheDir = '';
        $this->externalCacheDir = false;
        $this->forceRegenerateCache = false;

        $this->cacheSufix = 'CinnamonPHP.inc';
    }

    protected function GenerateCacheString($templateContent) {
        $matches = array();
        preg_match_all('/[^\\\\]({{([\\sa-z0-9\\.\\(\\)\\|\\,]+)}})/mi', $templateContent, $matches);
        $code = "<?php\r\n";
        foreach ($matches[1] as $key => $value) {
            $var = trim($matches[2][$key]);
            $code.='global $' . $var . ";\r\n";
            $templateContent = preg_filter('/(?<!\\\\)(' . $value . ')/', '<?php echo isset($' . $var . ') ? $' . $var . ' : ""; ?>', $templateContent, 1);
        }
        $code .= "ob_start();\r\n?>\r\n";
        $code.= $templateContent;
        return $code;
    }

    public function LoadTemplate($templateName, $compress = FALSE) {
        /**
         * @todo Normalize template name if it contains directory
         */
        $templateRealPath = '';
        $templateCacheRealPath = '';

        foreach ($this->templatePaths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $templateName)) {
                $templateRealPath = $path . DIRECTORY_SEPARATOR . $templateName;
                if ($this->externalCacheDir) {
                    $templateCacheRealPath = $this->externalCacheDir . DIRECTORY_SEPARATOR . $templateName . $this->cacheSufix;
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

        include $templateCacheRealPath;
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
            $code = preg_replace('~>\\s+<~m', '><', $code);
        }
        
        return $code;
    }

    public function ForceRegenerateCache($force) {
        return $this->forceRegenerateCache = $force;
    }

    public function SaveInCacheDir($save) {
        return $this->externalCacheDir = $save;
    }

    public function SetCacheDire($path, $forceCreate = FALSE) {
        $path = realpath($path);
        if (file_exists($path) && is_dir($path) && $path !== FALSE) {
            $this->externalCacheDir = TRUE;
            $this->cacheDir = $path;
            return TRUE;
        } elseif ($forceCreate === TRUE) {
            if (mkdir($path, 0777, TRUE) === TRUE) {
                return $this->SetExternalFolder($path);
            }
        }
        return FALSE;
    }

    public function RemoveCacheDire() {
        $this->externalCacheDir = false;
        $this->cacheDir = '';
        return TRUE;
    }

    public function AddTemplatePath($path) {
        $path = realpath($path);
        if (file_exists($path) && is_dir($path) && $path !== FALSE) {
            $this->templatePaths[] = $path;
            return TRUE;
        }
        return FALSE;
    }

    public function RemoveRemplatePath($path) {
        $path = realpath($path);
        if (($key = array_search($path, $this->$templatePaths) && $path !== FALSE) !== false) {
            unset($this->templatePaths[$key]);
            return TRUE;
        }
        return FALSE;
    }

}
