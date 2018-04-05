<?php
/**
 * @file
 * Contains \Drupal\contact_block\Plugin\Block\ContactBlock.
 */
namespace Drupal\contact_block\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "contact_block",
 *   admin_label = @Translation("contact block"),
 *   category = @Translation("Custom contact block example")
 * )
 */
class ContactBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {

    $country_url = $_SERVER['REDIRECT_country'];
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $base_path = drupal_get_path('module', 'contact_block');

    $db = \Drupal::database();

    $query = $db->select('node', 'n');
    $query->leftjoin('node__field_contact_country', 'nfcc', 'nfcc.entity_id = n.nid');
    $query->leftjoin('node__field_email', 'nfe', 'nfe.entity_id = n.nid');
    $query->leftjoin('node__field_number', 'nfn', 'nfn.entity_id = n.nid'); 
    $query->leftjoin('node__field_code', 'nfc', 'nfc.entity_id = n.nid');
    $query->fields('n', ['nid']);
    $query->fields('nfe', ['field_email_value']);
    $query->fields('nfcc', ['field_contact_country_value']);
    $query->fields('nfn', ['field_number_value']);
    $query->fields('nfc', ['field_code_value']);
    $query->condition('n.type', 'get_in_touch_contacts');
    $query->condition('nfcc.field_contact_country_value',$country_url);
    $result = $query->execute()->fetchObject();

    $output = '<div class="contact-block"
    <div class="column-right">
      <h2>'.$this->t('You can also get in touch this way:').'</h2>
      <ul class="other-info">';

    if(isset($result->field_email_value) && !empty($result->field_email_value)) {
      $output .= '<li>
          <span class="icon mail"><a href="mailto:'.$result->field_email_value.'"><i class="icon-ico-mail"></i></a></span>
          <h3>'.$this->t('Write to us').'</h3>
          <p>'.$this->t('Drop us a line and we’ll get back to you as fast as we can.').'</p>
          <a href="mailto:'.$result->field_email_value.'" class="link">'.$this->t('Email us now!').'</a>
        </li>';
    }
    
    $output .= '<li>
          <span class="icon facebook"><a href="https://www.facebook.com/BabyshopArabia" target="_blank"><i class="icon-facebook"></i></a></span>
          <h3>'.$this->t('Facebook us').'</h3>
          <p>'.$this->t('Connect with us on your favourite social network.').'</p>
        </li>
        <li>
          <span class="icon twitter"><a href="https://twitter.com/babyshoparabia" target="_blank"><i class="icon-twitter"></i></a></span>
          <h3>'.$this->t('Tweet us').'</h3>
          <p>'.$this->t('Reach out in 140<br> characters!').'</p>
          <a href="https://twitter.com/babyshoparabia" class="link">@babyshoparabia</a>
        </li>
        <li>
          <span class="icon store"><a href="stores"><img src="/'.$base_path.'/images/store-icon-2.png" /></a></span>
          <h3>'.$this->t('Reach Us').'</h3>
          <p>'.$this->t('Find out your <br>nearest').' <a href="stores" class="link">'.$this->t('store').'</a></p>
        </li>
      </ul>
    </div>
    </div>';

    return array(
      '#type' => 'markup',
      '#markup' => $output,
      '#cache' => ['max-age' => 0 ,],
    );
  }
}