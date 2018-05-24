<?php
putenv('LC_ALL='.$profile->get('locale'));
setlocale(LC_ALL, $profile->get('locale'));

bindtextdomain($profile->get('locale'), "./locale");

textdomain($profile->get('locale'));