<?php

    class DbOperations {
        
        private $con;

        function __construct() {
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect;
            $this->con = $db->connect();
        }
            
        public function createUser($name, $email, $password) {
            if(!$this->isEmailExists($email)) {
                $stmt = $this->con->prepare("insert into mt_users 
                        (user_name, user_email, user_password)
                        values (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $password);
                
                if($stmt->execute()) {
                    return USER_CREATED;                         
                } else {
                    return USER_FAILURE;
                }
            } 
            return USER_EXISTS;
        }

        public function userLogin($email, $password) {
             if($this->isEmailExists($email)) {
                 $hashedPassword = $this->getUsersPasswordByEmail($email);
                 if(password_verify($password, $hashedPassword)) {
                    return USER_AUTHENTICATED;
                 } else return USER_PASSWORD_DO_NOT_MATCH;
             } else {
                 return USER_NOT_FOUND;
             }
        }

        public function getUsersPasswordByEmail($email) {
            $stmt = $this->con->prepare("select user_password from mt_users where user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($password); 
            $stmt->fetch();
            return $password;
        }

        public function getUserByEmail($email) {
            $stmt = $this->con->prepare("select user_id, user_name, user_email from mt_users where user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id, $name, $email);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['name'] = $name;
            $user['email'] = $email; 
            return $user;
        }

        public function isEmailExists($email) {
            $stmt = $this->con->prepare("select user_id from mt_users where user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

    }