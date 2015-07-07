<?php

namespace DeLois\ProjectGenerator\Filter;

class TokenReplacementFilter extends \php_user_filter {

  const PARAM_KEY_SEARCH  = 'search';
  const PARAM_KEY_REPLACE = 'replace';

  const DEFAULT_NAME      = 'user.token.replacement';

  public function filter( $in, $out, &$consumed, $closing ) {

    $search  = $this->params[ self::PARAM_KEY_SEARCH ];
    $replace = $this->params[ self::PARAM_KEY_REPLACE ];

    while ( $bucket = stream_bucket_make_writeable( $in ) ) {

      $bucket->data = str_replace( $search, $replace, $bucket->data );
      $consumed     += $bucket->datalen;
      stream_bucket_append( $out, $bucket );

    }

    return PSFS_PASS_ON;

  }

}
