<?php

namespace Drupal\comingsoon_mode\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * The settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'comingsoon_mode_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['comingsoon_mode.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('comingsoon_mode.settings');

    $form['comingsoon_ckeck'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Put site into coming soon mode.'),
      '#description' => $this->t('Put site into coming soon mode.'),
      '#default_value' => $config->get('comingsoon_ckeck') ?? 0,
    ];

    $form['display_logo'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('display site logo.'),
      '#description' => $this->t('display logo.'),
      '#default_value' => $config->get('display_logo') ?? 0,
    ];

    $form['display_counter'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('display counter.'),
      '#description' => $this->t('display counter.'),
      '#default_value' => $config->get('display_counter') ?? 0,
    ];

    $form['background_image_ckeck'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the background image'),
      '#description' => $this->t('If checked, the image will be a background image.'),
      '#default_value' => $config->get('background_image_ckeck') ?? 0,
    ];

    $form['display_social_media_links'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('display social media links.'),
      '#description' => $this->t('display social media links.'),
      '#default_value' => $config->get('display_social_media_links') ?? 0,
    ];

    /* content */
    $form['content'] = [
      '#type' => 'details',
      '#title' => $this->t('Content'),
      '#open' => FALSE,
    ];

    $form['content']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title') ?? '',
      '#description' => $this->t('Enter the title.'),
    ];

    $form['content']['message'] = [
      '#type' => 'text_format',
      '#format' => 'restricted_html',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Enter the message.'),
      '#default_value' => $config->get('message') ? $config->get('message')['value'] : '',
    ];

    $form['content']['countdown_time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Countdown Time'),
      '#default_value' => !empty($config->get('countdown_time')) ? DrupalDateTime::createFromTimestamp($config->get('countdown_time')) : '',
      '#description' => $this->t('Enter the countdown time.'),
    ];

    /* style */
    $form['style'] = [
      '#type' => 'details',
      '#title' => $this->t('Style'),
      '#open' => FALSE,
    ];

    $form['style']['background_image'] = [
      '#type' => 'managed_file',
      '#name' => 'background_image',
      '#title' => $this->t('Background image'),
      '#size' => 20,
      '#upload_location' => 'public://settings_images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['svg jpg jpeg png'],
      ],
      '#default_value' => $config->get('background_image') ?? '',
    ];

    $form['style']['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background color'),
      '#default_value' => $config->get('background_color') ? $config->get('background_color') : '',
      '#description' => $this->t('Enter the background color, for example #ffffff.'),
    ];

    /* social media */
    $form['social_media'] = [
      '#type' => 'details',
      '#title' => $this->t('Social Media'),
      '#open' => FALSE,
    ];
    $form['social_media']['twitter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter'),
      '#default_value' => $config->get('twitter') ?? '',
      '#description' => $this->t('Enter the twitter link.'),
    ];

    $form['social_media']['facebook'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook'),
      '#default_value' => $config->get('facebook') ?? '',
      '#description' => $this->t('Enter the facebook link.'),
    ];

    $form['social_media']['instagram'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instagram'),
      '#default_value' => $config->get('instagram') ?? '',
      '#description' => $this->t('Enter the instagram link.'),
    ];

    $form['social_media']['linkedin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Linkedin'),
      '#default_value' => $config->get('linkedin') ?? '',
      '#description' => $this->t('Enter the linkedin link.'),
    ];

    $form['social_media']['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#default_value' => $config->get('email') ?? '',
      '#description' => $this->t('Enter the email address'),
    ];

    $form['social_media']['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#default_value' => $config->get('phone') ?? '',
      '#description' => $this->t('Enter the phone number.'),
    ];

    $form['permissions'] = [
      '#type' => 'details',
      '#title' => $this->t('Permissions'),
      '#open' => FALSE,
    ];

    $form['permissions']['permission_markup'] = [
      '#type' => 'markup',
      '#markup' => '<a href="/admin/people/permissions/module/comingsoon_mode">Set Permissions</a>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('background_color')) {
      if (!preg_match('/^#[a-f0-9]{6}$/i', $form_state->getValue('background_color'))) {
        $form_state->setErrorByName('background_color', $this->t('The background color is not valid.'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Make image permanent.
    SettingsForm::mkPermanent($form_state->getValue('background_image'));
    $skip = ['op', 'form_build_id', 'form_token', 'form_id', 'submit'];
    foreach ($form_state->getValues() as $key => $value) {
      if (!in_array($key, $skip)) {
        if ($key == 'countdown_time') {
          $value = $value ? $value->getTimestamp() : '';
          $this->config('comingsoon_mode.settings')
            ->set($key, $value)
            ->save();
        }
        else {
          $this->config('comingsoon_mode.settings')
            ->set($key, $value)
            ->save();
        }
      }
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Make file permanent function.
   */
  public function mkPermanent($file) {
    $permanent_file = $file;
    if (is_array($permanent_file)) {
      if (isset($permanent_file[0])) {
        $permanent_file_id = $permanent_file[0];
        if ($real_file = File::load($permanent_file_id)) {
          $real_file->setPermanent();
          $real_file->save();
        }
      }
    }
  }

}