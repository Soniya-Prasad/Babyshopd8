<?php
/**
 * @file
 * Contains \Drupal\site\Form\SubscribeForm.
 */

namespace Drupal\site\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Contribute form.
 */
class SubscribeForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'email_subscription_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['email'] = array(
			'#type' => 'email',
			'#required' => TRUE,
			'#attributes'=>array(
				'placeholder'=>$this->t('Please enter an email address'),
			),
	    );

		$form['search_button'] = array(
			'#type' => 'button',
			'#value'=> $this->t('Subscribe'),
			'#attributes' => array(
				'class' => array('btn')
			),
			'#ajax' => array(
				'callback' => '::submitForm', 
			), 
		);

		$form['#validate'][] = '::validateForm';


		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		// Validate email.
		if (filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL === false)) {
			$form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$response = new AjaxResponse();

		// Validate email.
		if (filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {

			$query = \Drupal::database()->select('newsletter_contact', 'nc');
			$query->fields('nc', ['id']);
			$query->condition('nc.email_id', $form_state->getValue('email'));
			$num_rows = $query->countQuery()->execute()->fetchField();

			if($num_rows > 0) {
				$css = ['border' => '1px solid red'];
				$response->addCommand(new CssCommand('#edit-email', $css));
				$response->addCommand(new HtmlCommand('.email-valid-message', $this->t('You are already subscribed.')));
			}
			else {
				$insert = \Drupal::database()->insert('newsletter_contact')
				->fields(
				  array(
				    'email_id' => $form_state->getValue('email'),
				    'subscribed' => 1,
				    'addtime' => time(),
				    'ip' => $_SERVER['REMOTE_ADDR'],
				    'country' => $_SERVER['REDIRECT_country'],
				  )
				)->execute();

				$css = ['border' => '1px solid #979797'];
	    		$response->addCommand(new HtmlCommand('.email-valid-message', ''));
	    		$response->addCommand(new CssCommand('#edit-email', $css));

	    		$message = '<h2>'.$this->t('Thank you for subscribing.').'</h2>';
				$response->addCommand(new HtmlCommand('#popup-content', $message));
			}
		}
		else {
			$css = ['border' => '1px solid red'];
			$response->addCommand(new CssCommand('#edit-email', $css));
			$response->addCommand(new HtmlCommand('.email-valid-message', $this->t('Please enter a valid e-mail address.')));
		}

		return $response;
	}
}
?>