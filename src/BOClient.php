<?php namespace FaulkJ\BOClient;
   /*
    * Interface Class for Business Objects v2.0
    *
    * Kopimi 2021 Joshua Faulkenberry
    * Unlicensed under The Unlicense
    * http://unlicense.org/
    */

   use \FaulkJ\WebClient\WebClient;

   class BOClient {
      const   version    = "2.0";

      private $authToken = null;
      private $debug     = false;
      private $webClient;
      private $user;
      private $ass;

      public function __construct(WebClient $webClient, $user, $pass, $debug = false) {
         $this->debug = $debug != false;
         $this->webClient = $webClient->debug($this->debug);
         $this->user  = $user;
         $this->pass  = $pass;
      }

      public function query($service, $options = null) {
         $req = $this->webClient->request([
            "target"  => $service,
            "headers" => $this->authToken ? "X-SAP-LogonToken: '{$this->authToken}'" : null,
            "method"  => isset($options["method"]) ? $options["method"] : "GET",
            "qs"      => isset($options["qs"]) ? $options["qs"] : null,
            "data"    => isset($options["data"]) ? $options["data"] : null,
            "type"    => "application/xml",
            "accept"  => "application/xml"
         ]);

         if($req->code != 200) trigger_error("Query failed:\n\n{$req->body}");

         $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $req->body);
         return $xml;
      }

      public function authenticate($callback = null) {
         if($auth = simplexml_load_string($this->query("logon/long", array(
            "method" => "POST",
            "data"   => "<attrs xmlns='http://www.sap.com/rws/bip'><attr name='userName' type='string'>".$this->user."</attr><attr name='password' type='string'>".$this->pass."</attr><attr name='auth' type='string' possibilities='secEnterprise,secLDAP,secWinAD'>secEnterprise</attr></attrs>"
         )))) {
            if(isset($auth->error_code)) {
               trigger_error("Error authenticating BO: " . $auth->message);
               return false;
            }
            elseif(!isset($auth->content->attrs->attr)) {
               trigger_error("Error authenticating BO.");
               return false;
            }
            else {
               $this->authToken = $auth->content->attrs->attr;
               if(is_callable($callback)) $callback();
               return true;
            }
         }
         else {
            trigger_error("Error authenticating BO.");
            return false;
         }
      }

      public function logoff($callback=null) {
         $this->query("logoff", array(
            "method" => "POST"
         ));
         $this->authToken = null;
         if(is_callable($callback)) $callback();
         return $this;
      }

      public function debug($debug) {
         $this->debug = $debug != false;
         return $this;
      }
   }
?>