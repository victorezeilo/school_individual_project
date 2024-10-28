<?php
//Paginator.class.php

class Paginator{

	private $position;
	private $page_count;
	private $rows_per_page; //Number of records to display per page
	private $links_per_page; //Number of links to display per page
	private $debug;
	
	
	function __construct($params){

		$this->position = isset($params['position']) ? filter_var($params['position'],FILTER_SANITIZE_NUMBER_INT) : NULL; 
		$this->page_count = isset($params['page_count']) ? filter_var($params['page_count'],FILTER_SANITIZE_NUMBER_INT) : NULL; 
		$this->rows_per_page = isset($params['rows_per_page']) ? filter_var($params['rows_per_page'],FILTER_SANITIZE_NUMBER_INT) : NULL;
		$this->links_per_page = isset($params['links_per_page']) ? filter_var($params['links_per_page'],FILTER_SANITIZE_NUMBER_INT) : NULL; 
		$this->debug = false; 
	}
	
	/**
	 * Display full pagination navigation
	 *
	 * @access public
	 * @return string
	 */

	public function paginate(){
		
		$result = NULL;
		$success =  true;
		
		if(filter_var($this->page_count,FILTER_VALIDATE_INT,array('options' => array('min_range' => 1))) === false){
			$success = false;
			$result = $this->debug ? 'Invalid page count parameter set' : 'No records found';	
		}
		elseif(filter_var($this->position,FILTER_VALIDATE_INT,array('options' => array('min_range' => 0))) === false){
			$success = false;
			$result = $this->debug ? 'Invalid position parameter set' : 'No records found';	
		}
		
		if($success){

			//ensure that current page does not have value less than 0 or more than total pages
			$this->position++;
			if ($this->position > $this->page_count || $this->position <= 0) {$this->position = 1;}
			
			$this->rows_per_page = $this->rows_per_page > 0 ? $this->rows_per_page : 20;
	
			//ensures that links per page does not execeed total pages
			$this->links_per_page = $this->links_per_page > 0 ?  $this->links_per_page : 10;
			if ($this->links_per_page > $this->page_count) {$this->links_per_page = $this->page_count;}
			
			//create pagination links
			$result = $this->renderFirst().' '.$this->renderPrev().' '.$this->renderNav().' '.$this->renderNext().' '.$this->renderLast();
		}
		return $result;

	}
	
	public function renderPage(){
		
		return  "Page $this->position of $this->page_count";
	}

	/**
	 * Display the link to the first page
	 *
	 * @access private
	 * @param string $tag Text string to be displayed as the link. Defaults to 'First'
	 * @return string
	 */
	private function renderFirst($tag = 'First') {

		$result = NULL;
		
		switch($this->position == 1){
			case true:
			$result = $tag;
			break;
			
			default:
			$result = '<a href="javascript:void(0);" data-position="0">'.$tag.'</a>'; 
		}
		
		return  $result;
	}
	
	/**
	 * Display the link to the last page
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to 'Last'
	 * @return string
	 */
	private function renderLast($tag = 'Last') {
		
		//return $this->position == $this->page_count ? $tag : '<a href="javascript:void(0)"  data-position="'.($this->page_count - 1).'">'.$tag.'</a>';

		$result = NULL;
		
		switch($this->position == $this->page_count ){
			case true:
			$result = $tag;
			break;
			
			default:
			$result = '<a href="javascript:void(0);" data-position="'.($this->page_count - 1).'">'.$tag.'</a>'; 
		}
		
		return  $result;
	}
	
	/**
	 * Display the next link
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to '>>'
	 * @return string
	 */
	function renderNext($tag = '&#10095;&#10095;') {
		
		$result = NULL;
		
		switch($this->position < $this->page_count ){
			case true:
			$result = '<a href="javascript:void(0);" data-position="'.($this->position).'">'.$tag.'</a>';
			break;
			
			default:
			$result = $tag; 
		}
		
		return  $result;
	}
	
	/**
	 * Display the previous link
	 *
	 * @access private
	 * @param string $tag Text string to be displayed as the link. Defaults to '<<'
	 * @return string
	 */
	private function renderPrev($tag = '&#10094;&#10094;') {
		
		$result = NULL;
		
		switch($this->position > 1 ){
			case true:
			$result = '<a href="javascript:void(0);" data-position="'.($this->position-2).'">'.$tag.'</a>';
			break;
			
			default:
			$result = $tag; 
		}
		
		return  $result;
	}
	
	/**
	 * Display the page links
	 *
	 * @access public
	 * @return string
	 */
	
	public function renderNav($prefix = '<span class="page_link">', $suffix = '</span>'){
		
		$links = '';

		$end = ceil($this->position / $this->links_per_page ) * $this->links_per_page;
		if ($end > $this->page_count) {$end = $this->page_count;}
		
		$start = $end - $this->links_per_page + 1;
		$floor = floor($this->page_count/$this->links_per_page);
		if($this->position > $floor * $this->links_per_page){$start = $floor * $this->links_per_page + 1;}
		
		for($i = $start; $i <= $end; $i++){
			
			$links .= $i == $this->position ? " <font class=\"position\">$i</font> " : ' <a href="javascript:void(0);" data-position="'.($i-1).'">'.$i.'</a> ';
		}
		return $links;
			
	}
	
	
	/**
	 * Set object debug option
	 *
	 * @access public
	 * @return nothing
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
	}
	
}
?>