<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator is a plugin which allows creation of custom forms of
 * easy access.
 * ---------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Formcreator.
 *
 * Formcreator is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Formcreator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Formcreator. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @copyright Copyright © 2011 - 2021 Teclib'
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @link      https://github.com/pluginsGLPI/formcreator/
 * @link      https://pluginsglpi.github.io/formcreator/
 * @link      http://plugins.glpi-project.org/#/plugin/formcreator
 * ---------------------------------------------------------------------
 */
namespace tests\units;
use GlpiPlugin\Formcreator\Tests\CommonTestCase;

class PluginFormcreatorCommon extends CommonTestCase {
   public function testGetFormcreatorRequestTypeId() {
      $requestTypeId = \PluginFormcreatorCommon::getFormcreatorRequestTypeId();

      // The ID must be > 0 (aka found)
      $this->integer((integer) $requestTypeId)->isGreaterThan(0);
   }

   public function testIsNotificationEnabled() {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = '0';
      $output = \PluginFormcreatorCommon::isNotificationEnabled();
      $this->boolean($output)->isFalse();

      $CFG_GLPI['use_notifications'] = '1';
      $output = \PluginFormcreatorCommon::isNotificationEnabled();
      $this->boolean($output)->isTrue();
   }

   public function testSetNotification() {
      global $CFG_GLPI;

      $CFG_GLPI['use_notifications'] = '1';
      \PluginFormcreatorCommon::setNotification(false);
      $this->integer((int) $CFG_GLPI['use_notifications'])->isEqualTo('0');

      \PluginFormcreatorCommon::setNotification(true);
      $this->integer((int) $CFG_GLPI['use_notifications'])->isEqualTo('1');
   }

   public function providerGetPictoFilename() {
      return [
         [
            'version' => '9.5.0',
            'expected' => 'font-awesome.php',
         ],
         [
            'version' => '9.9.0',
            'expected' => 'font-awesome.php',
         ]
      ];
   }

   /**
    * Undocumented function
    *
    * @dataProvider providerGetPictoFilename
    */
   public function testGetPictoFilename($version, $expected) {
      $output = \PluginFormcreatorCommon::getPictoFilename($version);
      $this->string($output)->isEqualTo($expected);
   }

   public function providerPrepareBooleanKeywords() {
      return [
         [
            'input' => '',
            'expected' => '',
         ],
         [
            'input' => 'foo bar',
            'expected' => 'foo* bar*',
         ],
         [
            'input' => 'foo bar ',
            'expected' => 'foo* bar*',
         ],
         [
            'input' => 'foo***** bar* ',
            'expected' => 'foo* bar*',
         ],
      ];
   }

   /**
    * @dataProvider providerPrepareBooleanKeywords
    */
   public function testPrepareBooleanKeywords($input, $expected) {
      $output = \PluginFormcreatorCommon::prepareBooleanKeywords($input);
      $this->string($output)->isEqualTo($expected);
   }

   public function testGetCaptcha() {
      unset($_SESSION['plugin_formcreator']['captcha']);

      $captchaId = 'someRandomId';
      $output = \PluginFormcreatorCommon::getCaptcha($captchaId);
      $this->array($output)->hasKeys([
         'img',
         'phrase'
      ])->size->isEqualTo(2);
      $this->array($_SESSION['plugin_formcreator']['captcha'])
         ->hasKeys([$captchaId])
         ->size->isEqualTo(1);
      $this->array($_SESSION['plugin_formcreator']['captcha'][$captchaId]);
   }

   public function testCheckCaptcha() {
      unset($_SESSION['plugin_formcreator']['captcha']);

      $captchaId = 'someRandomId';
      $output = \PluginFormcreatorCommon::getCaptcha($captchaId);
      $challenge = $_SESSION['plugin_formcreator']['captcha'][$captchaId]['phrase'];
      $output = \PluginFormcreatorCommon::checkCaptcha($captchaId, $challenge);
      $this->boolean($output)->isTrue();
      $output = \PluginFormcreatorCommon::checkCaptcha($captchaId, $challenge . 'foo');
      $this->boolean($output)->isFalse();
   }

   public function testCleanOldCaptchas() {
      $output = \PluginFormcreatorCommon::getCaptcha('captcha1');
      $challenge = $_SESSION['plugin_formcreator']['captcha']['captcha1']['phrase'];
      sleep(2); // Wait 5 seconds
      $output = \PluginFormcreatorCommon::getCaptcha('captcha2');
      $output = \PluginFormcreatorCommon::checkCaptcha('captcha1', $challenge, 1);
      $this->boolean($output)->isFalse();
      $this->array($challenge = $_SESSION['plugin_formcreator']['captcha'])
         ->notHasKey('captcha1');
   }
}
