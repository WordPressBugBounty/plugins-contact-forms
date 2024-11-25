<?php
if (!class_exists('AccuaConditionalReplacer')) {
  class AccuaConditionalReplacer {
    protected $map = array();
    protected $search = null;
    protected $replace = null;
    protected $matches = null;
    
    function __construct(array $map = array()){
      $this->map = $map;
    }
    
    function appendPattern(array $map = array()){
      $this->map = $map + $this->map;
      $this->search = null;
      $this->replace = null;
      $this->matches = null;
    }
    
    function setPattern(array $map = array()){
      $this->map = $map;
      $this->search = null;
      $this->replace = null;
      $this->matches = null;
    }
    
    function doReplace($subject) {
      if ($this->matches === null){
        $this->search = array();
        $this->replace = array();
        $this->matches = array();
        foreach ($this->map as $s => $r) {
          $s = (string) $s;
          $r = (string) $r;
          $this->search[] = '{'.$s.'}';
          $this->replace[] = $r;
          if (trim($r) !== '') {
            $this->matches[$s] = true;
          }
        }
      }
      
      return str_replace($this->search, $this->replace, preg_replace_callback(
        // /\[\s*newsletter_if\s*(\{[^\}]*\})\s*(then)?\s*("[^"]*"|'[^']*')\s*((else)?\s*("[^"]*"|'[^']*'))?\]/ims
        //'/\\[\\s*newsletter_if\\s*(\\{[^\\}]*\\})\\s*(then)?\\s*("[^"]*"|\'[^\']*\')\\s*((else)?\\s*("[^"]*"|\'[^\']*\'))?\\]/ims',
        // /\[\s*newsletter_if\s*(\{[^\}]*\})\s*(then|\?)?\s*("[^"]*"|'[^']*'|`[^`]*`|\[[^\]]*\])\s*((else|\:)?\s*("[^"]*"|'[^']*'|`[^`]*`|\[[^\]]*\]))?\s*\]/ims
        '/\\[\\s*(accua_if|newsletter_if|form_if)\\s*\\{([^\\}]*)\\}\\s*(then|\\?)?\\s*("[^"]*"|\'[^\']*\'|`[^`]*`|\\[[^\\]]*\\])\\s*((else|\\:)?\\s*("[^"]*"|\'[^\']*\'|`[^`]*`|\\[[^\\]]*\\]))?\\s*\\]/is',
        array($this, '_doReplaceMatch'),
        $subject
      ));
      
    }
    
    function _doReplaceMatch($match){
      if (isset($match[2]) && isset($this->matches[$match[2]])){
        @ $ret = $match[4];
      } else {
        @ $ret = $match[7];
      }
      if (is_string($ret) && strlen($ret) > 2){
        return substr($ret,1,-1);
      }
      return '';
    }
  }
}