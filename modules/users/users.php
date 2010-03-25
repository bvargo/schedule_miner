<?php

// Copyright 2009-2010 The Schedule Miner Authors
// Use of this source code is governed by an MIT-style license that can be
// found in the LICENSE file.

class Users extends Module
{
   function index()
   {
      global $SM_USER;

      if(!isset($SM_USER))
         redirect("users/create");
      else if($SM_USER->admin)
         redirect("users/show_list");
      else
         redirect("users/edit");
   }

   // shows a list of users
   // no arguments
   public function show_list()
   {
      global $SM_USER;

      if(!$SM_USER->admin)
      {
         $this->args['error'] = "You are not an admin.";
         return;
      }

      $user = new user();
      $results = $user->find("");
      $this->args['users'] = $results;
   }

   // edits a user
   // arguments: username
   public function edit()
   {
      global $SM_ARGS, $SM_USER;

      // if the username was not given as an argument, use the current user
      if(count($SM_ARGS) < 3)
      {
         $user = $SM_USER;
      }
      else
      {
         // a username was provided

         // make sure the user is an admin to edit someone else
         if(!$SM_USER->admin && $SM_USER->username != $SM_ARGS[2])
         {
            $this->args['error'] = "You are not an admin.";
            return;
         }

         $user = new user();
         if(!$user->load("username=?", array($SM_ARGS[2])))
            $user = null;
      }

      if($user)
      {
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
      global $SM_ARGS, $SM_USER;

      if(isset($SM_USER) && !$SM_USER->admin)
      {
         // if a user is logged in and they are not an admin, redirect to the
         // edit page
         redirect("users/edit");
      }

      // see if there is a POST request to create a user
      if(!empty($_POST))
      {
         // create the user

         // make sure all the fields are present and filled
         if(!isset($_POST['username']) ||
            !isset($_POST['name']) ||
            !isset($_POST['email']) ||
            !isset($_POST['password']) ||
            !isset($_POST['password_verify']) ||
            trim($_POST['username']) == "" ||
            trim($_POST['name']) == "" ||
            trim($_POST['email']) == "" ||
            trim($_POST['password']) == "" ||
            trim($_POST['password_verify']) == "")
         {
            $this->args['error'] = "You must complete all of the fields.";
            return;
         }

         // the two passwords must be the same
         if($_POST['password'] != $_POST['password_verify'])
         {
            // passwords do not match
            $this->args['error'] = "The entered passwords do not match.";
            return;
         }
         else
         {
            $user = new user();

            // see if the user already exists
            // if so, display an error message
            // if not, $user is still a new user
            $user->load("username=?", array($_POST['username']));
            if($user->id)
            {
               $this->args['error'] = "This username is already in use.";
               return;
            }
            else
            {
               $user->name = $_POST['name'];
               $user->username = $_POST['username'];
               $user->email = $_POST['email'];
               $user->password = $_POST['password'];
               $user->admin = 0;
               $user->save();
               if(isset($SM_USER) && $SM_USER->admin)
                  redirect("users/show_list");
               else
                  redirect();
            }
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
      global $SM_ARGS, $SM_USER;

      // if the username was not given as an argument, use the current user
      if(count($SM_ARGS) < 3)
      {
         $user = $SM_USER;
      }
      else
      {
         // a username was provided

         // make sure the user is an admin to edit someone else
         if(!$SM_USER->admin && $SM_USER->username != $SM_ARGS[2])
         {
            $this->args['error'] = "You are not an admin.";
            return;
         }

         $user = new user();
         if(!$user->load("username=?", array($SM_ARGS[2])))
            $user = null;
      }

      if($user)
      {
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
         if($user->load("username=?", array($_POST['username'])))
         {
            // user found, check the password
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
