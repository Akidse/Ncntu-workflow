<?php
putenv('LC_ALL='.$profile->get('locale'));
setlocale(LC_ALL, $profile->get('locale'));

bindtextdomain($profile->get('locale'), "./locale");
bind_textdomain_codeset($profile->get('locale'), 'UTF-8');

textdomain($profile->get('locale'));