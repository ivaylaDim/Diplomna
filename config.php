<?php
// Central configuration (keep secrets out of VCS in production).
// These default values are fallbacks for local dev only.
// Set environment variables RECAPTCHA_SITE_KEY and RECAPTCHA_SECRET in production.

defined('RECAPTCHA_SITE_KEY') || define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '6LdsWCAsAAAAAPicdWmvwvHBluHoAkaxg-NOFhQT');
defined('RECAPTCHA_SECRET')   || define('RECAPTCHA_SECRET',   getenv('RECAPTCHA_SECRET')   ?: '6LdsWCAsAAAAAFOwdRopAYv8aaB2we0trMTpr5jj');

// Add other config items below as needed.
