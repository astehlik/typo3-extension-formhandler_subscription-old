<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 23.12.11
 * Time: 16:23
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_Mailer_TYPO3Mailer  extends Tx_Formhandler_AbstractMailer implements Tx_Formhandler_MailerInterface {

	/**
	 * @var t3lib_mail_Message
	 */
	protected $emailObj;

	/**
	 * @var Swift_Mime_MimePart
	 */
	protected $htmlMimePart;

	/**
	 * @var Swift_Mime_MimePart
	 */
	protected $plainMimePart;

	public function __construct(Tx_Formhandler_Component_Manager $componentManager,
								Tx_Formhandler_Configuration $configuration,
								Tx_Formhandler_Globals $globals,
								Tx_Formhandler_UtilityFuncs $utilityFuncs) {

		parent::__construct($componentManager, $configuration, $globals, $utilityFuncs);
		$this->emailObj = t3lib_div::makeInstance('t3lib_mail_Message');
	}

	public function send($recipient) {
		if (!empty($recipient)) {
			$this->emailObj->setTo($recipient);
			$this->emailObj->send();
		}
	}

	public function setHTML($html) {

		if (!isset($this->htmlMimePart)) {
			$this->htmlMimePart = Swift_MimePart::newInstance($html, 'text/html');
		} else {
			$this->emailObj->detach($this->htmlMimePart);
			$this->htmlMimePart->setBody($html);
		}

		$this->emailObj->attach($this->htmlMimePart);
	}

	public function setPlain($plain) {

		if (!isset($this->plainMimePart)) {
			$this->plainMimePart = Swift_MimePart::newInstance($plain, 'text/plain');
		} else {
			$this->emailObj->detach($this->plainMimePart);
			$this->plainMimePart->setBody($plain);
		}

		$this->emailObj->attach($this->plainMimePart);
	}

	public function setSubject($value) {
		$this->emailObj->setSubject($value);
	}

	public function setSender($email, $name) {
		if (!empty($email)) {
			$this->emailObj->setSender($email, $name);
		}
	}

	public function setReplyTo($email, $name) {
		if (!empty($email)) {
			$this->emailObj->setReplyTo($email, $name);
		}
	}

	public function addCc($email, $name) {
		$this->emailObj->addCc($email, $name);
	}

	public function addBcc($email, $name) {
		$this->emailObj->addBcc($email, $name);
	}

	public function setReturnPath($value) {
		$this->emailObj->setReturnPath($value);
	}

	public function addHeader($value) {
		//@TODO: Since this is not working anyway at the moment we do not need to implement it right now
	}

	public function addAttachment($value) {
		$this->emailObj->attach(Swift_Attachment::fromPath($value));
	}

	public function getHTML() {
		if (isset($this->htmlMimePart)) {
			return $this->htmlMimePart->getBody();
		} else {
			return '';
		}
	}

	public function getPlain() {
		if (isset($this->plainMimePart)) {
			return $this->plainMimePart->getBody();
		} else {
			return '';
		}
	}

	public function getSubject() {
		return $this->emailObj->getSubject();
	}

	public function getSender() {
		return $this->emailObj->getSender();
	}

	public function getReplyTo() {
		return $this->emailObj->getReplyTo();
	}

	public function getCc() {
		return $this->emailObj->getCc();
	}

	public function getBcc() {
		return $this->emailObj->getBcc();
	}

	public function getReturnPath() {
		return $this->emailObj->getReturnPath();
	}
}
