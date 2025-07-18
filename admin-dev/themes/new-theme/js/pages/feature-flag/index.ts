/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

import ConfirmModal from '@components/modal';
import FeatureFlagMap from '@pages/feature-flag/components-map';

const {$} = window;

$(() => {
  const $submitButton = $(FeatureFlagMap.betaSubmitButton);
  const $stableFormSubmitButton = $(FeatureFlagMap.stableSubmitButton);
  const $form = $(FeatureFlagMap.betaForm);
  const $betaFormInputs = $(FeatureFlagMap.betaFormInputFields);
  const $stableForm = $(FeatureFlagMap.stableForm);
  const $stableFormInputs = $(FeatureFlagMap.stableFormInputs);
  const $stableFormInitialState = $stableForm.serialize();
  const initialState = $form.serialize();
  const initialFormData = $form.serializeArray();

  $betaFormInputs.on('change', () => {
    $submitButton.prop('disabled', initialState === $form.serialize());
  });

  $stableFormInputs.on('change', () => {
    $stableFormSubmitButton.prop('disabled', $stableFormInitialState === $stableForm.serialize());
  });

  $submitButton.on('click', (event) => {
    event.preventDefault();

    const formData = $form.serializeArray();

    if (initialState === $form.serialize()) {
      return;
    }

    let oneFlagIsEnabled = false;

    for (let i = 0; i < formData.length; i += 1) {
      if ((formData[i].name !== 'form[_token]') && (formData[i].value !== '0') && (initialFormData[i].value === '0')) {
        oneFlagIsEnabled = true;
        break;
      }
    }

    if (oneFlagIsEnabled) {
      new ConfirmModal(
        {
          id: 'modal-confirm-submit-feature-flag',
          confirmTitle: $submitButton.data('modal-title'),
          confirmMessage: $submitButton.data('modal-message'),
          confirmButtonLabel: $submitButton.data('modal-apply'),
          closeButtonLabel: $submitButton.data('modal-cancel'),
        },
        () => {
          $form.submit();
        },
      );
    } else {
      $form.submit();
    }
  });
});
