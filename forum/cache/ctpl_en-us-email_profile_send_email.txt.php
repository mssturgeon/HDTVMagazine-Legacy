<?php if (!defined('IN_PHPBB')) exit; ?>Hello <?php echo (isset($this->_rootref['TO_USERNAME'])) ? $this->_rootref['TO_USERNAME'] : ''; ?>,

The following is an e-mail sent to you by <?php echo (isset($this->_rootref['FROM_USERNAME'])) ? $this->_rootref['FROM_USERNAME'] : ''; ?> via your account on "<?php echo (isset($this->_rootref['SITENAME'])) ? $this->_rootref['SITENAME'] : ''; ?>". If this message is spam, contains abusive or other comments you find offensive please contact the webmaster of the board at the following address:

<?php echo (isset($this->_rootref['BOARD_CONTACT'])) ? $this->_rootref['BOARD_CONTACT'] : ''; ?>


Include this full e-mail (particularly the headers). Please note that the reply address to this e-mail has been set to that of <?php echo (isset($this->_rootref['FROM_USERNAME'])) ? $this->_rootref['FROM_USERNAME'] : ''; ?>.

Message sent to you follows
~~~~~~~~~~~~~~~~~~~~~~~~~~~

<?php echo (isset($this->_rootref['MESSAGE'])) ? $this->_rootref['MESSAGE'] : ''; ?>