<?php 
class DB {
	private $link;

	function __construct(){
		$this->link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)
		or die("Ошибка " . mysqli_error($link));
	}

	function query($query = ''){
		if($query){
			$result = mysqli_query($this->link, $query) 
			or die("Ошибка " . mysqli_error($this->link));

			$rows = array();
			
			if( $result->num_rows > 0 ){
				while( $row_ = mysqli_fetch_assoc($result) ){ 
					$row = $row_;
					$rows[] = $row_;
					break;
				}
				while( $row_ = mysqli_fetch_assoc($result) ){ 
					$rows[] = $row_;
				} 
				
			}
			
			return array('row'=>$row,'rows'=>$rows);
		}
		else{
			return false;
		}
	}

	function escape($str = ''){
		return mysqli_real_escape_string($this->link, $str);
	}

	function close(){
		mysqli_close($link);
	}
}