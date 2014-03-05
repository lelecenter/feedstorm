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

require_once 'model/story.php';

class search extends fs_controller
{
   public $query;
   public $stories;
   
   public function __construct()
   {
      parent::__construct('search', 'Buscar &lsaquo; '.FS_NAME);
      
      $this->query = '';
      $this->stories = array();
      
      if( isset($_POST['query']) )
      {
         $this->query = trim($_POST['query']);
         if( mb_strlen($this->query) >= 2 )
         {
            $story = new story();
            $this->stories = $story->search($this->query);
            
            if( count($this->stories) == 0 )
               $this->new_message('Sin resultados');
         }
         else
            $this->new_error_msg('Demasiado corto');
      }
   }
   
   public function get_description()
   {
      return 'El buscador de '.FS_NAME.'. Si no encuentras un artículo aquí es porque
         es irrelevante XD.';
   }
}

?>