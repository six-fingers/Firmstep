<?php

require_once 'API.class.php';

class CustomAPI extends API
{
    protected $conn;

    public function __construct($request, $origin) {
        parent::__construct($request);

        // Connection Parameters
        $servername = "localhost";
        $username = "root";
        $password = "root";
        $database_name = "FirmStep";
        $port = 8889;

        // Create connection
        $this->conn = new mysqli($servername, $username, $password, $database_name, $port);
    }

     //Method Selector function
     protected function queue() {
       if ($this->conn->connect_error) {
         return "Connection failed: " . $this->conn->connect_error;
       }

      if ($this->method == 'GET') {
        return $this->queue_get($this->request['type']);
      }

      if ($this->method == 'POST') {
        return $this->queue_post(
          $this->request['type'],
          $this->request['firstName'],
          $this->request['lastName'],
          $this->request['organization'],
          $this->request['service']
        );
      }

      return "Only accepts GET and POST requests";
     }

     protected function queue_get($type) {

       if($type && $this->type_validation($type) != NULL) {
         return $this->type_validation($type);
       }


       $sql = "SELECT * FROM queue where DATE(queuedDate) = CURDATE()";
       if($type) {
         $sql .= " AND type='$type'";
       }
       $query_result = $this->conn->query($sql);
       $this->conn->close();

       $results = "No results";
       if ($query_result->num_rows > 0) {
         $results = array();
         // push data into an array
         while($row = $query_result->fetch_assoc()) {
             array_push($results, $row);
         }
       }
       return $results;

     }

     protected function queue_post($type, $firstname, $lastname, $organization, $service) {
       if(!$type) {
         return "Type is mandatory";
       } else {
         if ($this->type_validation($type) != NULL) {
           return $this->type_validation($type);
         }
       }

       if($type == "Citizen") {
         if(!$firstname) {
           return "First Name is mandatory for Citizen type";
         }

         if(!$lastname) {
           return "Last Name is mandatory for Citizen type";
         }
       }

       if(!$service) {
         return "Service is mandatory";
       } else {
         if($service != 'Council Tax' && $service != 'Benefits' && $service != 'Rent' ) {
           return "Type Not Valid: If sent it must be 'Council Tax' or 'Benefits' or 'Rent'! ";
         }
       }

       $sql = "INSERT INTO queue (firstName, lastName, organization, `type`, service, queuedDate) VALUES ('$firstname', '$lastname', '$organization', '$type', '$service', now())";

       if ($this->conn->query($sql) === TRUE){
         $this->conn->close();
         return "Success: Correctly Added";
       } else {
         $this->conn->close();
         return $conn->error;
       }

     }

     //Created only to avoid duplicating code and to separate responsability
     protected function type_validation($type) {
       if($type != 'Citizen' && $type != 'Anonymous' ) {
         return "Type Not Valid: If sent it must be 'Citizen' or 'Anonymous' ! ";
       }
     }
 }

 ?>
