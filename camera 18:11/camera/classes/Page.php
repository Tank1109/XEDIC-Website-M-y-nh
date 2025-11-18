<?php


class Page {
    private $title;
    private $description;
    private $keywords;
    private $author;

    public function __construct() {
        $this->title = "XEDIC Camera";
        $this->description = "Camera Chuyên Nghiệp Cho Creators";
        $this->keywords = "camera, photography, video, creator, professional";
        $this->author = "XEDIC";
    }

    public function setTitle($title) {
        $this->title = htmlspecialchars($title);
    }

    public function getTitle() {
        return $this->title;
    }

    public function setDescription($description) {
        $this->description = htmlspecialchars($description);
    }

    public function getDescription() {
        return $this->description;
    }

    public function setKeywords($keywords) {
        $this->keywords = htmlspecialchars($keywords);
    }

    public function getKeywords() {
        return $this->keywords;
    }

    public function setAuthor($author) {
        $this->author = htmlspecialchars($author);
    }

    public function getAuthor() {
        return $this->author;
    }
}