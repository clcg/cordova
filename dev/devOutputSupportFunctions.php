<?php
class devStuff {
	
	public $devOutputStrings = array();
	
	public function printToDevOutputFile($content) {
	
		$file = './dev/MockConsoleOutput.txt';
		
		file_put_contents($file, $content);
		
	}
}