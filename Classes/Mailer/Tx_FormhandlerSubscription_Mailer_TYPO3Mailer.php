<?php

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Uses the TYPO3 mail functions for sending emails
 */
class Tx_FormhandlerSubscription_Mailer_TYPO3Mailer  extends Tx_Formhandler_AbstractMailer implements Tx_Formhandler_MailerInterface {

	/**
	 * The TYPO3 mail message object
	 *
	 * @var t3lib_mail_Message
	 */
	protected $emailObj;

	/**
	 * The html part of the message
	 *
	 * @var Swift_Mime_MimePart
	 */
	protected $htmlMimePart;

	/**
	 * The plain text part of the message
	 *
	 * @var Swift_Mime_MimePart
	 */
	protected $plainMimePart;

	/**
	 * Initializes the email object and calls the parent constructor
	 *
	 * @param Tx_Formhandler_Component_Manager $componentManager
	 * @param Tx_Formhandler_Configuration $configuration
	 * @param Tx_Formhandler_Globals $globals
	 * @param Tx_Formhandler_UtilityFuncs $utilityFuncs
	 */
	public function __construct(Tx_Formhandler_Component_Manager $componentManager,
								Tx_Formhandler_Configuration $configuration,
								Tx_Formhandler_Globals $globals,
								Tx_Formhandler_UtilityFuncs $utilityFuncs) {

		parent::__construct($componentManager, $configuration, $globals, $utilityFuncs);
		$this->emailObj = t3lib_div::makeInstance('t3lib_mail_Message');
	}

	/**
	 * Sends the message to the given recipient if the
	 * recipient is not empty
	 *
	 * The recipient can either be a single email address, an indexed array containing
	 * multiple email addresses (e.g. array('mail1@domain.tld', 'mail2@domain.tld'))
	 * or an associative array containing one or multiple email addresses and
	 * recipient names (e.g. array('mail1@domain.tld' => 'John Doe',
	 * 'mail2@domain.tld' => 'Bob Doe'))
	 *
	 * @param array|string $recipient the recepient(s) of the message
	 * @see Swift_Mime_Headers_MailboxHeader::normalizeMailboxes()
	 */
	public function send($recipient) {
		if (!empty($recipient)) {
			$this->emailObj->setTo($recipient);
			$this->emailObj->send();
		}
	}

	/**
	 * Sets the content of the html part of the message
	 *
	 * @param string $html
	 */
	public function setHTML($html) {

		if (!isset($this->htmlMimePart)) {
			$this->htmlMimePart = Swift_MimePart::newInstance($html, 'text/html');
		} else {
			$this->emailObj->detach($this->htmlMimePart);
			$this->htmlMimePart->setBody($html);
		}

		if (!empty($html)) {
			$this->emailObj->attach($this->htmlMimePart);
		}
	}

	/**
	 * Sets the content of the plain text part of the message
	 *
	 * @param string $plain
	 */
	public function setPlain($plain) {

		if (!isset($this->plainMimePart)) {
			$this->plainMimePart = Swift_MimePart::newInstance($plain, 'text/plain');
		} else {
			$this->emailObj->detach($this->plainMimePart);
			$this->plainMimePart->setBody($plain);
		}

		if (!empty($plain)) {
			$this->emailObj->attach($this->plainMimePart);
		}
	}

	/**
	 * Sets the subject of the mail
	 *
	 * @param string $value
	 */
	public function setSubject($value) {
		$this->emailObj->setSubject($value);
	}

	/**
	 * Sets the name and email of the mail sender
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function setSender($email, $name) {
		if (!empty($email)) {
			$this->emailObj->setSender($email, $name);
		}
	}

	/**
	 * Sets the name and email of the reply to contact
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function setReplyTo($email, $name) {
		if (!empty($email)) {
			$this->emailObj->setReplyTo($email, $name);
		}
	}

	/**
	 * Adds the name and email of a cc recipient
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function addCc($email, $name) {
		$this->emailObj->addCc($email, $name);
	}

	/**
	 * Adds the name and email of a bcc recipient
	 *
	 * @param string $email
	 * @param string $name
	 */
	public function addBcc($email, $name) {
		$this->emailObj->addBcc($email, $name);
	}

	/**
	 * Sets the return path to the given value
	 *
	 * @param string $value RFC 2822 compatible email address
	 */
	public function setReturnPath($value) {
		$this->emailObj->setReturnPath($value);
	}

	/**
	 * Adds a header to the message
	 *
	 * Does not work at the moment!
	 *
	 * @param $value
	 */
	public function addHeader($value) {
		//@TODO: Since this is not working anyway at the moment we do not need to implement it right now
	}

	/**
	 * Adds the given file as an attachment to the mail
	 *
	 * @param string $value path to the file to add
	 */
	public function addAttachment($value) {
		$this->emailObj->attach(Swift_Attachment::fromPath($value));
	}

	/**
	 * Returns the current html content
	 *
	 * @return string
	 */
	public function getHTML() {
		if (isset($this->htmlMimePart)) {
			return $this->htmlMimePart->getBody();
		} else {
			return '';
		}
	}

	/**
	 * Returns the current plain text content
	 *
	 * @return string
	 */
	public function getPlain() {
		if (isset($this->plainMimePart)) {
			return $this->plainMimePart->getBody();
		} else {
			return '';
		}
	}

	/**
	 * Returns the current subject
	 *
	 * @return string
	 */
	public function getSubject() {
		return $this->emailObj->getSubject();
	}

	/**
	 * Returns the current sender
	 *
	 * @return array containing the sender email and optionally the sender name
	 */
	public function getSender() {
		return $this->emailObj->getSender();
	}

	/**
	 * Returns the current reply to value
	 *
	 * @return array containing the reply-to address and optionally the reply-to name
	 */
	public function getReplyTo() {
		return $this->emailObj->getReplyTo();
	}

	/**
	 * Returns the current cc recepients in an array
	 *
	 * @return array containing the cc addresses and optionally the cc names
	 */
	public function getCc() {
		return $this->emailObj->getCc();
	}

	/**
	 * Returns the current bcc recepients in an array
	 *
	 * @return array containing the bcc addresses and optionally the bcc names
	 */
	public function getBcc() {
		return $this->emailObj->getBcc();
	}

	/**
	 * Returns the return path
	 *
	 * @return string RFC 2822 compatible email address
	 */
	public function getReturnPath() {
		return $this->emailObj->getReturnPath();
	}
}
?>