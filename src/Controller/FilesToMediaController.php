<?php

namespace Drupal\files_to_media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\media\Entity\Media;

/**
 * Returns responses for Files to Media routes.
 */
class FilesToMediaController extends ControllerBase {

  /**
   * Builds the response.
   * admin/config/media/files-to-media
   */
  public function build() {

    $database = \Drupal::database();
    $query = $database->query("SELECT fid, filename, filemime FROM {file_managed} WHERE fid NOT IN (SELECT fid FROM {file_usage} WHERE type = 'media') AND (filemime = 'image/jpeg' OR filemime = 'image/png' OR filemime = 'application/pdf' OR filemime = 'text/plain')");
    $filesindatabase = $query->fetchAll();
    $list = '';

    foreach ($filesindatabase as $key => $file) {
      $media = '';
      $mediadata = [];
      $user = 50; //   \Drupal::currentUser()->id(),

      if ($file->filemime == 'image/jpeg' || $file->filemime == 'image/png') {
        $mediadata = [
          'bundle' => 'image',
          'uid' => $user,
          'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
          'status' => 1,
          'field_media_image' => [
              'target_id' => $file->fid,
              'alt' => $file->filename,
          ],
        ];
      }

      if ($file->filemime == 'application/pdf' || $file->filemime == 'text/plain') {
        $mediadata = [
          'bundle' => 'file',
          'uid' => $user,
          'langcode' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
          'status' => 1,
          'field_media_file' => [
              'target_id' => $file->fid,
          ],
        ];
      }

      $media = Media::create($mediadata);
      // dpm($media);
      $media->save();
      $list .= '<br>'.$file->filename;
    }

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $list,
    ];

    return $build;
  }

}
