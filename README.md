# BOClient
Business Objects web services client


```
$bo = new BOClient(
   new WebClient("https://my.host.com"),
   "username",
   "password"
);

if($this->bo->authenticate()) {

   $list = [];
   if($lst = $bo->query("raylight/v1/universes", [
      "qs" => "limit=$limit&offset=$offset"
   ])) {
      $t = simplexml_load_string($lst);
      foreach ($t->universe as $u) {
         $uid = (int) $u->id;
         echo $uid;
      }
   }

   $bo->logoff();

}
```