<?php

/**
 * @file
 * Contains \Drupal\gist_embed\Plugin\Filter\GistEmbedFilter.
 */

namespace Drupal\gist_embed\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to Convert Mautic form form_id into embed link".
 *
 * @Filter(
 *   id = "gist_embed_filter",
 *   title = @Translation("Ultra powered gist embedding for your website"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
class GistEmbedFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $text = preg_replace_callback(
      '/\[embed_url: (.*?)\]/',
      function ($matches) {
        $replace = $this->replaceValues($matches[1]);

        return $replace;
      },
      $text
    );

    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Ultra powered gist embedding for your website.');
  }

  /**
   * @param $values
   * @return string
   */
  private function replaceValues($values) {
    /** TODO inject renderer service */
    $renderer = \Drupal::getContainer()->get('renderer');

    $client = \Drupal::httpClient();
    try {
      // get URL from link
      preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $values, $result);
      if (!empty($result)) {
        if(!empty($result['href'][0])) {
          $values=$result['href'][0];
        }
      }

      $request = $client->get($values);
      $status = $request->getStatusCode();
      $content = $request->getBody()->getContents();
    }
    catch (Exception $e) {
    }

    $elements = [
      '#theme' => 'gist_embed_filter',
      '#gist_data' => $content,
    ];

    return $renderer->render($elements);
  }
}
