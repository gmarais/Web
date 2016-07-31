<?php

class Mail
{
	// headers variables
	private $_content_type = 'multipart/alternative;';
	private $_subject;
	private $_from = _MAIL_SENDER_;
	private $_reply_to;
	private $_to;
	private $_Cc;
	private $_Bcc;
	private $boundary;
	private $eol = "\n";

	// content template
	private $_template_name;
	private $_tpl_vars = array();

	public function __construct($subject, $to, $template_name)
	{
		if (!is_array($to))
			$to = array($to);
		$this->_to = $to;
		$this->_subject = $subject;
		$this->_template_name = $template_name;
		$this->boundary = "-----=".md5(rand());
	}

	public function assign($key, $value)
	{
		$this->_tpl_vars[$key] = $value;
	}

	public function addTo($to)
	{
		if (!is_array($to))
			$to = array($to);
		$this->_to = array_merge($this->_to, $to);
	}

	public function addCc($Cc)
	{
		if (!is_array($Cc))
			$Cc = array($Cc);
		$this->_Cc = array_merge($this->_Cc, $Cc);
	}

	public function addBcc($Bcc)
	{
		if (!is_array($Bcc))
			$Bcc = array($Bcc);
		$this->_Bcc = array_merge($this->_Bcc, $Bcc);
	}

	public function addReplyTo($reply_to)
	{
		if (!is_array($reply_to))
			$reply_to = array($reply_to);
		$this->_reply_to = array_merge($this->_reply_to, $reply_to);
	}

	public function setFrom($from)
	{
		$this->_from = $from;
	}

	private function fetchContent()
	{
		$tpl = new Tpl();
		foreach ($this->_tpl_vars as $key => $value) {
			$tpl->assign($key, $value);
		}
		$htmlContent = $tpl->fetch('mail/'.$this->_template_name.".html");
		$htmlContent = wordwrap($htmlContent, 70, $this->eol);
		$textContent = $tpl->fetch('mail/'.$this->_template_name.".txt");
		$textContent = wordwrap($textContent, 70, $this->eol);

		$content = $this->eol."--".$this->boundary.$this->eol
		."Content-Type: text/plain; charset=\"utf-8\"".$this->eol
		."Content-Transfer-Encoding: 8bit".$this->eol
		.$this->eol.$textContent.$this->eol
		.$this->eol."--".$this->boundary.$this->eol
		."Content-Type: text/html; charset=\"utf-8\"".$this->eol
		."Content-Transfer-Encoding: 8bit".$this->eol
		.$this->eol.$htmlContent.$this->eol
		.$this->eol."--".$this->boundary."--".$this->eol
		.$this->eol."--".$this->boundary."--".$this->eol;

		return $content;
	}


	public function send()
	{
		$headers = ($this->_from ? 'From: '.$this->_from.$this->eol : '')
		.($this->_reply_to ? 'Reply-to: '.implode(',', $this->_reply_to).$this->eol : '')
		.($this->_Cc ? 'Cc: '.implode(',', $this->_Cc).$this->eol : '')
		.($this->_Bcc ? 'Bcc: '.implode(',', $this->_Bcc).$this->eol : '')
		.'MIME-Version: 1.0'.$this->eol
		.($this->_content_type ? 'Content-type: '.$this->_content_type.$this->eol : '')
		." boundary=\"$this->boundary\"".$this->eol;

		mail(implode(',', $this->_to), $this->_subject, $this->fetchContent(), $headers);
	}
}