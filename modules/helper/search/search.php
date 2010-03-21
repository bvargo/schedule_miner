<?php
class Search
{
   // search client
   private $sc;

   // index to search
   private $index;

   // any error messages
   public $error = false;

   function __construct($match_mode = NULL)
   {
      $this->sc = new SphinxClient();

      // set the server and search port
      $server = smconfig_get("server", "localhost");
      $port = smconfig_get("port", 9312);
      $this->sc->SetServer($server, $port);

      // set the match mode
      if($match_mode)
      {
         $this->sc->SetMatchMode($match_mode);
      }
      else
      {
         $match_mode = smconfig_get("match_mode", "extended2");
         if($match_mode == "all")
            $this->sc->SetMatchMode(SPH_MATCH_ALL);
         else if($match_mode == "any")
            $this->sc->SetMatchMode(SPH_MATCH_ANY);
         else if($match_mode == "phrase")
            $this->sc->SetMatchMode(SPH_MATCH_PHRASE);
         else if($match_mode == "boolean")
            $this->sc->SetMatchMode(SPH_MATCH_BOOLEAN);
         else if($match_mode == "extended")
            $this->sc->SetMatchMode(SPH_MATCH_EXTENDED);
         else if($match_mode == "extended2")
            $this->sc->SetMatchMode(SPH_MATCH_EXTENDED2);
         else if($match_mode == "fullscan")
            $this->sc->SetMatchMode(SPH_MATCH_FULLSCAN);
      }

      // set the maximum number of results to return
      $max_results = smconfig_get("max_results", 1000);
      $this->sc->setLimits(0, $max_results);

      // set the catalog to search
      $this->index = smconfig_get("index");
      if(!$this->index)
         warn("No search catalog specified");
   }

   private function check_error()
   {
      if($this->sc->getLastError())
      {
         $this->error = $this->sc->getLastError();
         return true;
      }
      else if($this->sc->getLastWarning())
      {
         $this->error = $this->sc->getLastWarning();
         return true;
      }
      $this->error = false;
      return false;
   }

   public function course_sections($search_query)
   {
      // return if an empty search query is given
      if(trim($search_query) == "")
         return array();

      $results = $this->sc->Query($search_query, $this->index);

      if($this->check_error($results))
         return false;

      $matches = $results['matches'];

      if(empty($matches))
         return array();

      $course_sections = array();
      foreach($matches as $section => $document_info)
      {
         $s = new course_section();
         if($s->load("id=?", array($section)))
         {
            $s->weight = $document_info["weight"];
            $course_sections[] = $s;
         }
      }

      return $course_sections;
   }
}
?>
