<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

class Users extends Module
{
   function index()
   {
      redirect("users/show_list");
   }

   // shows a list of users
   // no arguments
   public function show_list()
   {
      $user = new user();
      $results = $user->Find('');
      $this->args['users'] = $results;
   }

   // edits a user
   // arguments: username
   public function edit()
   {
      global $SM_ARGS;

      // if the username was not given as an argument, redirect to the list of
      // users
      if(count($SM_ARGS) < 3)
      {
         redirect("users/show_list");
      }

      // find the requested user
      $user = new user();
      $results = $user->Find("username=?", array($SM_ARGS[2]));
      if(count($results))
      {
         $user = $results[0];
         $this->args['user'] = $user;

         // see if there is a POST request to update a user
         if(!empty($_POST))
         {
            // there is data - update the user and pass the updated user to the
            // template
            $user->name = $_POST['name'];
            $user->email = $_POST['email'];
            $success = $user->save();
            $this->args['update_success'] = $success;
            $this->args['user'] = $user;
         }
      }
      else
      {
         $this->args['username'] = $SM_ARGS[2];
      }
   }

   // adds a user
   // if no arguments, show the add user form
   // if arguments, then add the user
   public function create()
   {
      global $SM_ARGS;

      // see if there is a POST request to create a user
      if(!empty($_POST))
      {
         // create the user

         // the two passwords must be the same
         if($_POST['password'] != $_POST['password_verify'])
         {
            // passwords do not match
            // FIXME
         }
         else
         {
            // FIXME - check for all parameters
            $user = new user();

            $results = $user->Find("username=?", array($_POST['username']));
            if(count($results))
            {
               // user already exists
               $user = $results[0];
            }
            $user->name = $_POST['name'];
            $user->username = $_POST['username'];
            $user->email = $_POST['email'];
            $user->password = $_POST['password'];
            $user->save();
            redirect("users/show_list");
         }
      }
      else
      {
         // no data - show the form to create a user

         // if the username is given, pass it to the template
         if(count($SM_ARGS) >= 3)
         {
            $this->args['username'] = $SM_ARGS[2];
         }
      }
   }

   // change a user's password
   public function change_password()
   {
      global $SM_ARGS;

      // if the username was not given as an argument, redirect to the list of
      // users
      if(count($SM_ARGS) < 3)
      {
         redirect("users/show_list");
      }

      // find the requested user
      $user = new user();
      $results = $user->Find("username=?", array($SM_ARGS[2]));
      if(count($results))
      {
         $user = $results[0];
         $this->args['user'] = $user;

         // see if there is a POST request to update a user
         if(!empty($_POST))
         {
            // there is data - update the user
            $user->password = $_POST['password'];
            $success = $user->save();
            $this->args['update_success'] = $success;
         }
      }
      else
      {
         $this->args['username'] = $SM_ARGS[2];
      }
   }

   // logs in
   public function login()
   {
      global $SM_LOG;

      // see if a username/password were provided
      if(!empty($_POST))
      {
         $user = new user();

         $results = $user->Find("username=?", array($_POST['username']));
         if(count($results))
         {
            // user found, check the password
            $user = $results[0];
            if($user->check_password($_POST['password']))
            {
               // success
               global $SM_ROOT;

               // set the username in the session
               $_SESSION['username'] = $_POST['username'];

               // log the successful authentication
               $SM_LOG->log_auth($_SESSION['username'], true);

               // get the referring URL, to see if we should redirect
               // somewhere
               $ref = $_SERVER["HTTP_REFERER"];

               if(strpos($ref, $SM_ROOT) !== FALSE)
               {
                  // the referring website was this application

                  // if the referring page was the login page, redirect to the
                  // homepage, else redirect to the referring page
                  if(strpos($ref, $SM_ROOT."/users/login") !== FALSE)
                  {
                     redirect();
                  }
                  else
                  {
                     redirect($ref, 1);
                  }
               }

               // no referrer, so redirect to the home page
               redirect();
            }
            else
            {
               // bad password
               $this->args['error'] = 1;

               // log the error
               $SM_LOG->log_auth($_POST['username'], false);
            }
         }
         else
         {
            // user not found
            $this->args['error'] = 1;

            // log the error
            $SM_LOG->log_auth($_POST['username'], false);
         }
      }
      if(isset($_SESSION['username']))
         $this->args['username'] = $_SESSION['username'];
   }

   public function logout()
   {
      SessionManager::session_destroy();
      redirect();
   }
}

?>
