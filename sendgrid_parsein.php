<?php

Class SendgridParse {
	
	private function parseEmailAddress($raw) {
		$name = "";
		$email = trim($raw, " '\"");
		if (preg_match("/^(.*)<(.*)>.*$/", $raw, $matches)) {
			array_shift($matches);
			$name = trim($matches[0], " '\"");
			$email = trim($matches[1], " '\"");
		}
		return array(
			"name" => $name,
			"email" => $email,
			"full" => $name . " <" . $email . ">"
		); 
	}
	
	private function parseEmailAddresses($raw) {
		$arr = array();
		foreach(explode(",", $raw) as $email)
			$arr[] = $this->parseEmailAddress($email);
		return $arr;
	} 
	
	function __construct($post = NULL, $files = NULL) {
		if (!@$post)
			$post = $_POST;
		if (!@$files)
			$files = $_FILES;		
		$this->post = $post;
		$this->files = $files;
		
		$this->headers = @$post["headers"];
		$this->text = @$post["text"];
		$this->html = @$post["html"];
		$this->fromRaw = @$post["from"];
		$this->from = $this->parseEmailAddress(@$this->fromRaw);
		$this->toRaw = @$post["to"];
		$this->to = $this->parseEmailAddresses(@$this->toRaw);
		$this->ccRaw = @$post["cc"];
		$this->cc = $this->parseEmailAddresses(@$this->ccRaw);
		$this->subject = @$post["subject"];
		$this->dkimRaw = @$post["dkim"];
		$this->dkim = json_decode(@$this->dkimRaw);
		$this->spfRaw = @$post["SPF"];
		$this->spf = json_decode(@$this->spfRaw);
		$this->charsetsRaw = @$post["charsets"];
		$this->charsets = json_decode(@$this->charsetsRaw);
		$this->envelopeRaw = @$post["envelope"];
		$this->envelope = json_decode(@$this->envelopeRaw);
		$this->spam_score = @$post["spam_score"];
		$this->spam_report = @$post["spam_report"];
		
		$this->attachments = array();
		foreach ($files as $key=>$value){
			$this->attachments[] = $value;
		}



		//
		$lines = preg_split("/(\r?\n|\r)/", $this->headers);

		//print_r($lines);

		foreach ($lines as $line) {

			//echo '<br>'.$line;
			

			$line = str_replace('<br>', '', $line);
			$line = str_replace('<br />', '', $line);
			$line = str_replace('\n', '', $line);
			$line = str_replace("\r", '', $line);
			$line = str_replace('<', '', $line);
			$line = str_replace('>', ',', $line);

			$line = nl2br($line);
			$line = str_replace('<br />', '', $line);

			$aux = explode(':', $line);

			//print_r($aux);
			
			//
			if( $aux[0]=='Message-ID' ){
				$message_id = $aux[1];
				$message_id = str_replace(',', '', $message_id);
			}

			//
			if( $aux[0]=='References' ){
				$references = $aux[1];
				$references = str_replace(' ', '', $references);
				$references = trim($references, ',');
			}

			//
			if( $aux[0]=='In-Reply-To' ){
				$in_reply_to = $aux[1];
				$in_reply_to = str_replace(',', '', $in_reply_to);
			}



		}


		$this->message_id = $message_id;
		$this->references = $references;
		$this->in_reply_to = $in_reply_to;













	}
	
}

?>