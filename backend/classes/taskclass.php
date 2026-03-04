<?php
    class Task{
        private $id;
        private $category;
        private $description;
        private $complete;

        public function getId(){
            return $this->id;
        }
        public function getCategory(){
            return $this->category;
        }
        public function getDescription(){
            return $this->description;
        }
        public function getComplete(){
            return $this->complete;
        }
        public function setId($id){
            $this->id = $id;
        }
        public function setCategory($category){
            $this->category = $category;
        }
        public function setDescription($description){
            $this->description = $description;
        }
        public function setComplete($complete){
            $this->complete = $complete;
        }
    }
?>