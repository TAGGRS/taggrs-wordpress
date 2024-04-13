<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function tggr_hash_email($email)
{
    return hash('sha256', strtolower(trim($email)));
}