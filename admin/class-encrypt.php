<?php
/**
 * Encrypts info for secret things that need top secret clearence via the sacred
 * dark art of encryption 
 * 
 * (actually this class is used for the uploader in setting
 * up database info without calling the forbidden wordpress configuration file at
 * root level that is off limits to plugins to call directly)
 * 
 * @package    grfx
 */

class grfx_Encryption
{
    const CIPHER = MCRYPT_RIJNDAEL_128; // Rijndael-128 is AES
    const MODE   = MCRYPT_MODE_CBC;

    /* Cryptographic key of length 16, 24 or 32. NOT a password! */
    private $key;
    public function __construct($key) {
        $this->key = $key;
    }

    /**
     * Encrypts stuff
     * @param type $plaintext
     * @return type
     */
    public function encrypt($plaintext) {
        $ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);
        $ciphertext = mcrypt_encrypt(self::CIPHER, $this->key, $plaintext, self::MODE, $iv);
        return base64_encode($iv.$ciphertext);
    }

    /**
     * Decrypts stuff
     * @param type $ciphertext
     * @return type
     * @throws Exception
     */
    public function decrypt($ciphertext) {
        $ciphertext = base64_decode($ciphertext);
        $ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
        if (strlen($ciphertext) < $ivSize) {
            throw new Exception('Missing initialization vector');
        }

        $iv = substr($ciphertext, 0, $ivSize);
        $ciphertext = substr($ciphertext, $ivSize);
        $plaintext = mcrypt_decrypt(self::CIPHER, $this->key, $ciphertext, self::MODE, $iv);
        return rtrim($plaintext, "\0");
    }
    
    /**
     * Decrypts and sets up ini stuff
     * @param type $file
     * @return type
     */
    public function get_ini_file($file){
        
        $string = file_get_contents($file);
        $ini = $this->decrypt($string);
        $settings = parse_ini_string($ini);
        
        return $settings;
    }
    
}