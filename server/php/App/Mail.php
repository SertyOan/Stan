<?php
namespace App;

class Mail {
	private static
		$domain = 'local',
		$priorities = Array(1 => '1 (Highest)', 2 => '2 (High)', 3 => '3 (Normal)', 4 => '4 (Low)', 5 => '5 (Lowest)');

	private
		$to = Array(),
		$cc = Array(),
		$bcc = Array(),
		$headers = Array(),
		$charset,
		$encoding = '7bit',
		$receipt = false,
		$autoCheck = true,
		$attachments = Array(),
		$inlineParts = 0,
		$textBody,
		$htmlBody,
		$iCalendarBody;

	public static function checkAddress($address) { // check an email address validity
		if(preg_match('/.*<(.+)>/', $address, $matches)) {
			$address = $matches[1];
		}

		return (filter_var($address, FILTER_VALIDATE_EMAIL) !== false);
	}

	public static function getDomain($domain) {
		return self::$domain;
	}

	public static function setDomain($domain) {
		self::$domain = (String) $domain;
	}

	public function __construct($charset = 'UTF-8') {
		$this->charset = $charset;
	}

	public function autoCheck($bool = true) { // enable/disable the email addresses validator
		$this->autoCheck = (Boolean) $bool;
	}

	public function setSubject($subject) { // define the subject line of the email
		$subject = strtr($subject, "\r\n" , '  ');
		$this->headers['Subject'] = '=?utf-8?B?'.base64_encode($subject).'?=';
	}

	public function setSender($address) { // set the sender of the mail
		if($this->autoCheck && !self::checkAddress($address)) {
			throw new \Exception('Invalid email address');
		}

		$matches = Array();

		if(preg_match('/(.*)<(.*)>/', $address, $matches) == 1) {
			$address = '=?utf-8?B?'.base64_encode($matches[1]).'?= <'.$matches[2].'>';
		}

		$this->headers['From'] = $this->encodeAddress($address);
	}

	public function replyTo($address) { // set the "Reply-To" header 
		if($this->autoCheck && !self::checkAddress($address)) {
			throw new \Exception('Invalid email address');
		}

		$matches = Array();

		if(preg_match('/(.*)<(.*)>/', $address, $matches) == 1) {
			$address = '=?utf-8?B?'.base64_encode($matches[1]).'?= <'.$matches[2].'>';
		}

		$this->headers['Reply-To'] = $this->encodeAddress($address);
	}
 
	public function addReceipt($bool = true) { // add a receipt, confirmation returned to "From" address or to "Reply-To" if defined
		$this->receipt = (Boolean) $bool;
	}

	public function organization($organization) { // set the "Organization" header
		if(trim($organization) !== '') {
			$this->headers['Organization'] = $organization;
		}
	}

	public function priority($priority) { // set the mail priority
		if(!isset(self::$priorities[$priority])) {
			throw new \Exception('Invalid priority');
		}

		$this->headers['X-Priority'] = self::$priorities[$priority];
	}

	public function addRecipient() { // add mail recipient(s)
		$this->addRecipients(func_get_args(), 'to');
	}

	public function addRecipientCC() { // add carbon copy
		$this->addRecipients(func_get_args(), 'cc');
	}

	public function addRecipientBCC() { // add blind carbon copy
		$this->addRecipients(func_get_args(), 'bcc');
	}

	public function textBody($text, $charset = '') { // set the text body of the mail, define the charset if the message contains extended characters (accents)
		$this->textBody = $text;

		if($charset !== '') {
			$this->charset = strtolower($charset);

			if($this->charset !== 'us-ascii') {
				$this->encoding = '8bit';
			}
		}
	}

	public function htmlBody($html, $charset = '') { // set the html body of the mail, define the charset if the message contains extended characters (accents)
		$this->htmlBody = $html;

		if($charset !== '') {
			$this->charset = strtolower($charset);

			if($this->charset !== 'us-ascii') {
				$this->encoding = '8bit';
			}
		}
	}

	public function iCalendarBody($iCalendar) {
		$this->iCalendarBody = $iCalendar;
	}

	public function attach($filename, $fileType = null, $disposition = 'inline') { // attach a file to the mail
		if(is_null($fileType)) {
			$fileType = 'application/x-unknown-content-type';
		}

		if($disposition === 'inline') {
			$this->inlineParts++;
			$id = 'part'.$this->inlineParts.'.';

			for($i = 0; $i < 8; $i++) {
				$id .= rand(0, 9);
			}

			$id .= '.';

			for($i = 0; $i < 8; $i++) {
				$id .= rand(0, 9);
			}

			$id .= '@'.self::$domain;
		}
		else {
			$id = null;
		}

		$this->attachments[] = Array('filename' => $filename, 'fileType' => $fileType, 'disposition' => $disposition, 'id' => $id);
		return $id;
	}

	public function send() {
		// TODO verify mandatory data
		$to = implode(', ', $this->to);
		// DEBUG debug($to);
		$subject = $this->headers['Subject'];
		// DEBUG debug($subject);
		$message = $this->buildBody();
		// DEBUG debug($headers);
		$headers = $this->buildHeaders();
		// DEBUG debug($message);
		return @mail($to, $subject, $message, $headers, '-r '.$this->headers['From'].' -f '.$this->headers['From']);
	}

	private function buildHeaders() {
		if(sizeof($this->cc) > 0) {
			$this->headers['CC'] = implode(', ', $this->cc);
		}

		if(sizeof($this->bcc) > 0) {
			$this->headers['BCC'] = implode(', ', $this->bcc);
		}

		if($this->receipt) {
			if(isset($this->headers['Reply-To'])) {
				$this->headers['Disposition-Notification-To'] = $this->headers['Reply-To'];
			}
			else {
				$this->headers['Disposition-Notification-To'] = $this->headers['From'];
			}
		}

		$this->headers['X-Mailer'] = 'PHP/Mail';
		$this->headers['Date'] = date('D, d M Y G:i:s O');
		$this->headers['Message-ID'] = '<'.md5(uniqid()).'@'.self::$domain.'>';
		$this->headers['MIME-Version'] = '1.0';

		$headers = '';

		foreach($this->headers as $key => $value) {
			if($key === 'Subject') {
				continue;
			}

			$header = trim($key.': '.$value);

			if(strlen($header) > 998) {
				throw new \Exception('At least one mail header line is too long');
			}

			$headers .= $header."\r\n";
		}

		return $headers;
	}

	private function buildBody() { // check and encode attached file(s)
		$mainBoundary = $this->generateBoundary();
		$attachmentsCount = sizeof($this->attachments);

		if($attachmentsCount > 0) {
			$this->headers['Content-Type'] = 'multipart/related; boundary="'.$mainBoundary.'"';
		}
		else {
			$this->headers['Content-Type'] = 'multipart/alternative; boundary="'.$mainBoundary.'"';
		}

		$fullBody = "\r\n";

		if($attachmentsCount > 0) {
			$currentBoundary = $this->generateBoundary();
			$fullBody .= 'Content-Type: multipart/alternative; boundary="'.$currentBoundary.'"'."\r\n\n"
				.'--'.$mainBoundary."\r\n";
		}
		else {
			$currentBoundary = $mainBoundary;
		}

		if(!empty($this->textBody)) {
			$fullBody .= '--'.$currentBoundary."\r\n".
				'Content-Type: text/plain; charset='.$this->charset."\r\n".
				'Content-Transfer-Encoding: '.$this->encoding."\r\n\n".
				$this->textBody;
		}

		if(!empty($this->htmlBody)) {
			$fullBody .= '--'.$currentBoundary."\r\n".
				'Content-Type: text/html; charset='.$this->charset."\r\n".
				'Content-Transfer-Encoding: '.$this->encoding."\r\n\n".
				$this->htmlBody;
		}

		if(!empty($this->iCalendarBody)) {
			$fullBody .= '--'.$currentBoundary."\r\n".
				'Content-Type: text/calendar; method=REQUEST; charset='.$this->charset."\r\n".
				'Content-Transfer-Encoding: 8bit'."\r\n\n".
				$this->iCalendarBody;
		}

		$fullBody .= "\r\n".'--'.$currentBoundary.'--'."\r\n";

		if($attachmentsCount > 0) {
			$separator = chr(13).chr(10);
			$attachments = Array();

			for($i = 0; $i < $attachmentsCount; $i++) {
				$filename = $this->attachments[$i]['filename'];

				if(!file_exists($filename)) {
					continue;
				}

				$basename = basename($filename);
				$fileType = $this->attachments[$i]['fileType'];
				$disposition = $this->attachments[$i]['disposition'];
				$id = $this->attachments[$i]['id'];
				$subHeader = "\r\n".'--'.$this->boundary."\r\n".
					'Content-Type: '.$fileType.'; name="'.$basename.'"'."\r\n".
					'Content-Transfer-Encoding: '.$this->attachments[$i]['encoding']."\r\n".
					(empty($id) ? '' : 'Content-ID: <'.$id.'>'."\n").
					'Content-Disposition: '.$disposition.'; filename="'.$basename.'"'."\r\n\n";
				$attachments[] = $subHeader;
				$attachments[] = chunk_split(base64_encode(file_get_contents($filename)));
			}

			$fullBody .= implode($separator, $attachments);
			$fullBody .= "\r\n".'--'.$mainBoundary.'--'."\r\n";
		}

		return $fullBody;
	}

	private function generateBoundary() {
		return md5(uniqid());
	}

	private function addRecipients($addresses, $type) {
		$c = sizeof($addresses);

		if($c === 0) {
			throw new \Exception('No address passed as argument');
		}

		for($i = 0; $i < $c; $i++) {
			if($this->autoCheck && !self::checkAddress($addresses[$i])) {
				throw new \Exception('Invalid email address');
			}

			$address = $addresses[$i];
			$matches = Array();

			if(preg_match('/(.*)<(.*)>/', $address, $matches) == 1) {
				$address = '=?utf-8?B?'.base64_encode($matches[1]).'?= <'.$matches[2].'>';
			}

			$this->{$type}[] = $this->encodeAddress($address);
		}
	}

	private function encodeAddress($address) {
		$matches = Array();

		if(preg_match('/(.*)<(.*)>/', $address, $matches) == 1) {
			$address = (mb_detect_encoding($matches[1]) != 'ASCII'
				? '=?utf-8?B?'.base64_encode($matches[1]).'?='
				: $matches[1])
				.' <'.$matches[2].'>';
		}

		return $address;
	}
}
