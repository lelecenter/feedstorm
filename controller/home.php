<?php
/*
 * This file is part of FeedStorm
 * Copyright (C) 2014  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'model/comment.php';
require_once 'model/story_preview.php';

class home extends fs_controller
{
   public $preview;
   public $stories;
   
   public function __construct()
   {
      parent::__construct('home', 'Portada &lsaquo; '.FS_NAME);
      
      $this->noindex = FALSE;
      $this->preview = new story_preview();
      $this->stories = $this->visitor->last_stories();
      
      if( $this->visitor->admin )
      {
         $comment = new comment();
         $feedbacks = $comment->all4thread();
         if( count($feedbacks) > 0 )
         {
            if( isset($_COOKIE['last_feedback']) )
            {
               if($feedbacks[0]->get_id() != $_COOKIE['last_feedback'])
               {
                  setcookie('last_feedback', $feedbacks[0]->get_id(), time()+FS_MAX_AGE, FS_PATH);
                  $this->new_message('Tienes comentarios de feedback por <a href="'.FS_PATH.'feedback">leer</a>.');
               }
            }
            else
            {
               setcookie('last_feedback', $feedbacks[0]->get_id(), time()+FS_MAX_AGE, FS_PATH);
               $this->new_message('Tienes comentarios de feedback por leer.');
            }
         }
      }
      
      if(count($this->get_errors()) + count($this->get_messages()) == 0 AND mt_rand(0, 19) == 0)
      {
         $this->new_message('Si tienes problemas, dudas o sugerencias ¡No te cortes! Usa el <a href="'.FS_PATH.'feedback">formulario de contacto</a>.');
      }
   }
}

?>