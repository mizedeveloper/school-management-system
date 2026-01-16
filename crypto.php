<?php
include_once 'config.php';

function criptografarSenha($senha) {
    return openssl_encrypt($senha, 'aes-256-cbc', AES_KEY, 0, AES_IV);
}

function descriptografarSenha($senha_criptografada) {
    return openssl_decrypt($senha_criptografada, 'aes-256-cbc', AES_KEY, 0, AES_IV);
}
