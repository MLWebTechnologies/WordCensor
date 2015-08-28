<?php
/************************************************************************************
 Title          WordCensor - Content Plugin for Joomla 1.6x - 3.x
 Author         Mike Leeper
 Version        3.0.0
 Copyright      © by Mike Leeper
 License        This is free software and you may redistribute it under the GPL.
                WordCensor comes with absolutely no warranty. For details, see the 
                license at http://www.gnu.org/licenses/gpl.txt
                YOU ARE NOT REQUIRED TO KEEP COPYRIGHT NOTICES IN
                THE HTML OUTPUT OF THIS SCRIPT. YOU ARE NOT ALLOWED
                TO REMOVE COPYRIGHT NOTICES FROM THE SOURCE CODE.
************************************************************************************/
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
jimport('joomla.plugin.plugin');
class plgContentWordCensor extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$document	= JFactory::getDocument();
		$docType = $document->getType();
		if($docType != 'html') return; 
		$this->loadLanguage();
	}
	public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
  	if ( !$this->params->get( 'enabled', 1 ) ) {
	   	return true;
	   }
        if(!empty($row->title)) $row->title = $this->badword_replace($row->title);
        if(!empty($row->text)) $row->text = $this->badword_replace($row->text);
    }
  public function badword_replace($string)
   {
    $config_filter_method 	= $this->params->get('filter_method');
    $config_replace_method 	= $this->params->get('replacement_method');
    $config_bad_words 	= $this->params->get('bad_words');
    $use_badword_list 	= $this->params->get('word_list');
    $config_allowed_words 	= $this->params->get('allowed_words');
    $allowwordsarr = preg_split('/[,]/',$config_allowed_words, -1, PREG_SPLIT_NO_EMPTY);
    $config_replace_word 	= $this->params->get('replace_word');
    $config_replace_text 	= $this->params->get('replace_all_text');
    $config_custom_filter  = $this->params->get('custom_filter');
    if($config_filter_method == 0) {    
      $config_reg_expression = '/\b%badword%\b/i';
    } elseif($config_filter_method == 1) {
      $config_reg_expression = $config_custom_filter;
    }
    if($use_badword_list == 0){
      if($config_bad_words != ""){
        $arr = preg_split('/[,]/',$config_bad_words, -1, PREG_SPLIT_NO_EMPTY);
      } else {
        return $string;
      }
    } elseif($use_badword_list == 1) {
      $arr = file(JPATH_ROOT.'/plugins/content/wordcensor/wordcensor/badwords.txt');
    } elseif($use_badword_list == 2){
      $arr1 = file(JPATH_ROOT.'/plugins/content/wordcensor/wordcensor/badwords.txt');
      $arr2 = preg_split('/[,]/',$config_bad_words, -1, PREG_SPLIT_NO_EMPTY);
      $arr = array_merge($arr1,$arr2);
    }
    if(!$config_replace_method) {
      $replace_word = str_replace('%badword%','('.join("|",array_map('trim',$arr)).')',$config_reg_expression);
      if(preg_match($replace_word, $string, $match)) {
        $matcharray = preg_grep($replace_word,preg_split("/[\W]+/",$string,-1,PREG_SPLIT_NO_EMPTY));
        $stringarray = str_word_count($string,2);
        foreach($matcharray as $value){
          if(!in_array($value, $allowwordsarr)){
            $replace_chars = substr(str_repeat($config_replace_word,ceil(strlen($value)/strlen($config_replace_word))),0,strlen($value));
            $keys = array_keys($stringarray,$value,true);
            foreach($keys as $key){
              $string = substr_replace($string, $replace_chars, $key, strlen($value));
            }
          }
       }
     }
    } elseif($config_replace_method) {
      $replace_all = str_replace('%badword%','('.join("|",array_map('trim',$arr)).')',$config_reg_expression);
      if (preg_match($replace_all, $string, $match)) {
        $matcharray = preg_grep($replace_all,preg_split("/[\W]+/",$string,-1,PREG_SPLIT_NO_EMPTY));
        foreach($matcharray as $value){
          if(!in_array($value, $allowwordsarr)){
            $string = $config_replace_text;
            return $string;
		      }
        }
      }
    }
   return $string;
  }
}
?>