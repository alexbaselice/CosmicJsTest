<?php

<?php
// define variables and set to empty values
$nameErr = $emailErr = $genderErr = $websiteErr = "";
$name = $email = $gender = $comment = $website = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["name"])) {
    $nameErr = "Name is required";
  } else {
    $name = test_input($_POST["name"]);
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
      $nameErr = "Only letters and white space allowed";
    }
  }
  
  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    // check if e-mail address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }
    
  if (empty($_POST["website"])) {
    $website = "";
  } else {
    $website = test_input($_POST["website"]);
    // check if URL address syntax is valid (this regular expression also allows dashes in the URL)
    if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$website)) {
      $websiteErr = "Invalid URL";
    }
  }

  if (empty($_POST["comment"])) {
    $comment = "";
  } else {
    $comment = test_input($_POST["comment"]);
  }

  if (empty($_POST["gender"])) {
    $genderErr = "Gender is required";
  } else {
    $gender = test_input($_POST["gender"]);
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>

<h2>PHP Form Validation Example</h2>
<p><span class="error">* required field.</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
  Name: <input type="text" name="name" value="<?php echo $name;?>">
  <span class="error">* <?php echo $nameErr;?></span>
  <br><br>
  E-mail: <input type="text" name="email" value="<?php echo $email;?>">
  <span class="error">* <?php echo $emailErr;?></span>
  <br><br>
  Website: <input type="text" name="website" value="<?php echo $website;?>">
  <span class="error"><?php echo $websiteErr;?></span>
  <br><br>
  Comment: <textarea name="comment" rows="5" cols="40"><?php echo $comment;?></textarea>
  <br><br>
  Gender:
  <input type="radio" name="gender" <?php if (isset($gender) && $gender=="female") echo "checked";?> value="female">Female
  <input type="radio" name="gender" <?php if (isset($gender) && $gender=="male") echo "checked";?> value="male">Male
  <span class="error">* <?php echo $genderErr;?></span>
  <br><br>
  <input type="submit" name="submit" value="Submit">  
</form>

<?php

include("curl.php");

$curl = new Curl;

class Cosmic {

  // Construct objects
  private function constructObjects($data){
    
    // Get objects
    $objects = $data->objects;
    $cosmic = new stdClass();
    $cosmic->objects = new stdClass();
    $cosmic->objects->all = $objects;

    foreach($objects as $object){
      
      $slug = $object->slug;
      $type_slug = $object->type_slug;

      if($object->metafields){
        
        $metafields = $object->metafields;
        
        // Construct metafields
        foreach($metafields as $metafield){
          
          $key = $metafield->key;
          $object->metafield[$key] = $metafield;
        }
      }

      // Construct type
      $cosmic->objects->type[$type_slug][] = $object;

      $cosmic->object[$slug] = $object;

    }

    return $cosmic;
  }
  
  // Get all objects
  public function getObjects(){

    global $config, $curl;
    $url = $config->objects_url . "?read_key=" . $config->read_key;
    $data = json_decode($curl->get($url));
    
    $cosmic = $this->constructObjects($data);

    return $cosmic;

  }

  // Get media
  public function getMedia(){

    global $config, $curl;

    $data = json_decode($curl->get($config->media_url));
    return $data->media;

  }

  // Init all
  public function init(){

    $cosmic = $this->getObjects();
    $cosmic->media = $this->getMedia();
    
    return $cosmic;

  }

  // Add object
  public function addObject($params){

    global $config, $curl;
    $data = $curl->post($config->add_object_url, $params);

    return $data;

  }

  // Edit object
  public function editObject($params){

    global $config, $curl;

    $data = $curl->put($config->edit_object_url, $params);

    return $data;

  }

  // Delete object
  public function deleteObject($params){

    global $config, $curl;

    $data = $curl->delete($config->delete_object_url, $params);

    return $data;

  }

}

$cosmic_class = new Cosmic;

// Init everything
$cosmic = $cosmic_class->init();
$cosmic_objects = $cosmic->objects->all;

$cosmic = array();

// Set all metafields to key->value (array)
foreach($cosmic_objects as $object){
  $cosmic[$object->slug] = $object;
  $cosmic[$object->slug]->metafield = array();
  foreach($cosmic[$object->slug]->metafields as $metafield){
    $cosmic[$object->slug]->metafield[$metafield->key] = $metafield->value;
  }
}
