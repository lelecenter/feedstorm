<?php
/*
 * This file is part of FeedStorm
 * Copyright (C) 2013  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_once 'base/fs_mongo.php';

abstract class fs_model
{
   private static $mongo;
   private static $errors;
   private static $messages;
   
   protected $collection_name;
   protected $collection;
   
   protected $id;
   
   public function __construct($cname='test')
   {
      if( !defined('FS_MAX_AGE') )
         define('FS_MAX_AGE', 2592000);
      
      if( !defined('FS_TIMEOUT') )
         define('FS_TIMEOUT', 3);
      
      if( !isset(self::$mongo) )
         self::$mongo = new fs_mongo();
      
      if( !isset(self::$errors) )
         self::$errors = array();
      
      if( !isset(self::$messages) )
         self::$messages = array();
      
      $this->collection_name = $cname;
      $this->collection = self::$mongo->select_collection($cname);
   }
   
   public function get_id()
   {
      return $this->id;
   }
   
   public function id2str()
   {
      return (string)$this->id;
   }
   
   protected function set_id($id)
   {
      $this->id = new MongoId($id);
   }
   
   protected function new_error($msg=FALSE)
   {
      if($msg)
         self::$errors[] = (string)$msg;
   }
   
   protected function new_message($msg=FALSE)
   {
      if($msg)
         self::$messages[] = (string)$msg;
   }
   
   public function get_errors()
   {
      return self::$errors;
   }
   
   public function get_messages()
   {
      return self::$messages;
   }
   
   public function clean_errors()
   {
      self::$errors = array();
   }
   
   public function clean_messages()
   {
      self::$messages = array();
   }
   
   protected function add2history($comm)
   {
      self::$mongo->add2history($comm);
   }
   
   /// functión auxiliar para facilitar el uso de fechas
   public function time2timesince($v)
   {
      if( isset($v) )
      {
         $time = time() - $v;
         
         if($time < 0)
         {
            $time = abs($time);
            if($time <= 60)
               return $time.' segundos en el futuro WTF!';
            else if($time <= 3600)
               return round($time/60,0).' minutos en el futuro WTF!';
            else if($time <= 86400)
               return round($time/3600,0).' horas en el futuro WTF!';
            else if($time <= 604800)
               return round($time/86400,0).' dias en el futuro WTF!';
            else if($time <= 2592000)
               return round($time/604800,0).' semanas en el futuro WTF!';
            else if($time <= 29030400)
               return round($time/2592000,0).' meses en el futuro WTF!';
            else
               return 'más de un año en el futuro WTF!';
         }
         else if($time <= 60)
            return 'hace '.$time.' segundo(s)';
         else if($time <= 3600)
            return 'hace '.round($time/60,0).' minuto(s)';
         else if($time <= 86400)
            return 'hace '.round($time/3600,0).' hora(s)';
         else if($time <= 604800)
            return 'hace '.round($time/86400,0).' dia(s)';
         else if($time <= 2592000)
            return 'hace '.round($time/604800,0).' semana(s)';
         else if($time <= 29030400)
            return 'hace '.round($time/2592000,0).' mes(es)';
         else
            return 'hace '.round($time/31556736,0).' año(s)';
      }
      else
         return 'fecha desconocida';
   }
   
   public function ucfirst($string, $encoding='utf-8')
   {
      $strlen = mb_strlen($string, $encoding);
      $firstChar = mb_substr($string, 0, 1, $encoding);
      $then = mb_substr($string, 1, $strlen - 1, $encoding);
      return mb_strtoupper($firstChar, $encoding) . $then;
   }
   
   public function uncut($str)
   {
      /// Eliminamos cualquier rastro de &#8203; y del caracter de espacio raro
      return str_replace('&#8203;', '', str_replace('​', '', $str) );
   }
   
   /*
    * Esta función devuelve una copia del string $str al que se le ha
    * añadido un caracter de espacio vacío &#8203; cada $max_width
    * caracteres a cada palabra de más de $max_width caracteres.
    * Los caracteres escapados de HTML se cuentan como uno solo.
    */
   public function true_word_break($str, $max_width=30)
   {
      $str = $this->uncut($str);
      
      $chrArray = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
      $pos0 = 0;
      $width = 0;
      while( $pos0 < count($chrArray) )
      {
         $char = $chrArray[$pos0];
         
         if($char == ' ')
            $width = 0;
         else if($char == '&')
         {
            /// nos saltamos los elementos escapados
            for($pos1 = $pos0 + 1; $pos1 < min( array($pos0 + 9, count($chrArray)) ) ; $pos1++)
            {
               if( $chrArray[$pos1] == ';' )
               {
                  $pos0 = $pos1;
                  break;
               }
            }
            $width++;
         }
         else if($width >= $max_width)
         {
            $chrArray[$pos0] = '&#8203;' . $chrArray[$pos0];
            $width = 0;
         }
         else
            $width++;
         
         $pos0++;
      }
      
      $str = '';
      foreach($chrArray as $ch)
         $str .= $ch;
      
      return $str;
   }
   
   /*
    * Esta función devuelve una copia de $str con una longitud máxima de
    * $max_t_width caracteres, pero sin cortar la última palabra.
    * 
    * Además añade un caracter de espacio vacío cada $max_w_width
    * caracteres usando la functión fs_model::true_word_break().
    * 
    * Las elementos HTML son escapados mediante fs_model::no_html().
    */
   public function true_text_break($str, $max_t_width=500, $max_w_width=30)
   {
      $desc = $this->true_word_break( $this->no_html($str), $max_w_width );
      
      if( mb_strlen($desc) <= $max_t_width )
      {
         return trim($desc);
      }
      else
      {
         $description = '';
         
         foreach(explode(' ', $desc) as $aux)
         {
            if( mb_strlen($description.' '.$aux) < $max_t_width-3 )
            {
               if($description == '')
                  $description = $aux;
               else
                  $description .= ' ' . $aux;
            }
            else
               break;
         }
         
         return trim($description).'...';
      }
   }
   
   public function random_string($length = 10)
   {
      return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"),
              0, $length);
   }
   
   public function var2str($var)
   {
      if( is_null($var) )
         return NULL;
      else
         return (string)$var;
   }
   
   /*
    * Esta función convierte:
    * < en &lt;
    * > en &gt;
    * " en &quot;
    * ' en &#39;
    * 
    * Además elimina los espacios extra.
    * 
    * No tengas la tentación de sustiturla por htmlentities o htmlspecialshars
    * porque te encontrarás con muchas sorpresas desagradables.
    */
   public function no_html($t)
   {
      $newt = str_replace(' ', ' ', $t); /// sustituye el caracter de espacio raro
      $newt = str_replace('<', '&lt;', $newt);
      $newt = str_replace('>', '&gt;', $newt);
      $newt = str_replace('"', '&quot;', $newt);
      return str_replace("'", '&#39;', $newt);
   }
   
   public function remove_bad_utf8($str)
   {
      /// Reemplazamos las putas comillas
      $str = str_replace('“', '&quot;', $str);
      $str = str_replace('”', '&quot;', $str);
      $str = str_replace('‘', '&#39;', $str);
      $str = str_replace('’', '&#39;', $str);
      $str = str_replace('&#8220;', '&quot;', $str);
      $str = str_replace('&#8221;', '&quot;', $str);
      
      /// convertimos a utf8
      ini_set('mbstring.substitute_character', "none");
      return mb_convert_encoding($str, 'UTF-8', 'auto');
   }
   
   abstract public function get($id);
   abstract public function exists();
   abstract public function save();
   abstract public function delete();
   abstract public function all();
   abstract public function install_indexes();
   abstract public function cron_job();

   public function count()
   {
      $this->add2history(__CLASS__.'::'.__FUNCTION__);
      return $this->collection->count();
   }
   
   public function show_count()
   {
      return number_format($this->count(), 0, ',', '.');
   }
   
   public function curl_download($url, $googlebot=TRUE, $timeout=FS_TIMEOUT)
   {
      $ch0 = curl_init($url);
      curl_setopt($ch0, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch0, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch0, CURLOPT_FOLLOWLOCATION, true);
      
      if($googlebot)
         curl_setopt($ch0, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
      
      $html = curl_exec($ch0);
      curl_close($ch0);
      
      return $html;
   }
   
   public function curl_save($url, $filename, $googlebot=FALSE, $followlocation=FALSE)
   {
      $ch = curl_init($url);
      $fp = fopen($filename, 'wb');
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_TIMEOUT, FS_TIMEOUT);
      
      if($followlocation)
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
      
      if($googlebot)
         curl_setopt($ch, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
      
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
   }
}
