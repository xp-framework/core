<?php namespace util;

/**
 * @test  xp://net.xp_framework.unittest.util.URICanonicalizationTest
 */
class URICanonicalization {
  private static $defaults= [
    'http'  => 80,
    'https' => 443,
  ];


  private function normalize($segment, $default) {
    if (null === $segment) return $default;

    // Normalize escape sequences - https://tools.ietf.org/html/rfc3986#section-2.3
    $segment= preg_replace_callback(
      '/%([0-9a-zA-Z]{2})/',
      function($match) {
        $code= hexdec($match[1]);
        if (
          $code >= 65 && $code <= 90 ||                                 // A-Z
          $code >= 97 && $code <= 122 ||                                // a-z
          $code >= 48 && $code <= 57 ||                                 // 0-9
          95 === $code || 45 === $code || 46 === $code || 126 === $code // -._~
        ) {
          return chr($code);
        } else {
          return strtoupper($match[0]);
        }
      },
      $segment
    );

    // Remove Dot Segments - https://tools.ietf.org/html/rfc3986#section-5.2.4
    $output= '';
    while ('' !== $segment) {

      // A. If the input begins with a prefix of "../" or "./", then remove
      // that prefix from the input buffer; otherwise,
      if (preg_match('!^(\.\./|\./)!', $segment)) {
        $segment= preg_replace('!^(\.\./|\./)!', '', $segment);
      }

      // B. if the input buffer begins with a prefix of "/./" or "/.",
      // where "." is a complete path segment, then replace that
      // prefix with "/" in the input buffer; otherwise,
      else if (preg_match('!^(/\./|/\.$)!', $segment, $matches)) {
        $segment= preg_replace('!^'.$matches[1].'!', '/', $segment);
      }

      // C. if the input buffer begins with a prefix of "/../" or "/..",
      // where ".." is a complete path segment, then replace that
      // prefix with "/" in the input buffer and remove the last
      // segment and its preceding "/" (if any) from the output
      // buffer; otherwise,
      else if (preg_match('!^(/\.\./|/\.\.$)!', $segment, $matches)) {
        $segment= preg_replace('!^'.preg_quote($matches[1], '!').'!', '/', $segment);
        $output= preg_replace('!/([^/]+)$!', '', $output);
      }

      // D. if the input buffer consists only of "." or "..", then remove
      // that from the input buffer; otherwise,
      else if (preg_match('!^(\.|\.\.)$!', $segment)) {
        $segment= preg_replace('!^(\.|\.\.)$!', '', $segment);
      }

      // E. move the first path segment in the input buffer to the end of
      // the output buffer, including the initial "/" character (if
      // any) and any subsequent characters up to, but not including,
      // the next "/" character or the end of the input buffer.
      else if (preg_match('!(/*[^/]*)!', $segment, $matches)) {
        $segment= preg_replace('/^'.preg_quote($matches[1], '/').'/', '', $segment, 1);
        $output.= $matches[1];
      }
    }
    return $output;
  }

  /**
   * Canonicalize a given URI, returning a new one
   *
   * @param  util.URI $uri
   * @return util.URI
   */
  public function canonicalize(URI $uri) {
    sscanf($uri->scheme(), '%[^+]', $scheme);

    $creation= (new URICreation($uri))
      ->scheme(strtolower($scheme))
      ->path($this->normalize($uri->path(), '/'))
      ->query($this->normalize($uri->query(), null))
      ->fragment($this->normalize($uri->fragment(), null))
    ;

    if ($authority= $uri->authority()) {
      $creation->authority(new Authority(
        strtolower($authority->host()),
        $authority->port() === (self::$defaults[$scheme] ?? null) ? null : $authority->port(),
        $authority->user(),
        $authority->password()
      ));
    }
    return $creation->create();
  }
}