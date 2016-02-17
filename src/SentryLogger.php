<?php
/*
 * Copyright (C) 2015 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 3.0+ of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  General Public License for more details.
 *
 * You should have received a copy of the GNU General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 *
 * OR
 *
 * Copyright (C) 2015, Adam Schubert
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * * Neither the name of test nor the names of its
 *  contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Salamek\RavenNette;

use Exception;
use Tracy\Debugger;
use Tracy\Logger;

/**
 * Description of sentryLogger
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class SentryLogger extends Logger
{
  private $raven;
  private $enabled = true;


  public function __construct($dsn, $inDebug = false,  $directory = null, $email = null, $autoWire = true)
  {
    parent::__construct($directory, $email, Debugger::getBlueScreen());

    //Check for production mode, you will want to fllod sentry only in production... right ?
    $this->enabled = Debugger::$productionMode || $inDebug;

    $this->raven = new \Raven_Client($dsn);

    if ($autoWire)
    {
      //Add sentryNetteLogger to tracy
      $that = $this;
      Debugger::$onFatalError[] = function($e) use($that)
      {
        $that->onFatalError($e);
      };

      // Add logger to tracy
      Debugger::setLogger($this);
    }
  }

  public function log($message, $priority = self::INFO)
  {
    if ($this->enabled)
    {
      $exceptionFile = '';
      if ($this->directory && is_dir($this->directory))
      {
        $exceptionFile = $message instanceof Exception ? $this->getExceptionFile($message) : NULL;
        $line = $this->formatLogLine($message, $exceptionFile);
        $file = $this->directory . '/' . strtolower($priority ?: self::INFO) . '.log';

        if (!@file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX))
        {
          throw new \RuntimeException("Unable to write to log file '$file'. Is directory writable?");
        }
      }

      if ($message instanceof Exception)
      {
        $this->raven->captureException($message);

        if ($this->directory && is_dir($this->directory))
        {
          $this->logException($message, $exceptionFile);
        }
      }
      else
      {
        if (in_array($priority, array(self::ERROR, self::EXCEPTION, self::CRITICAL, self::WARNING), TRUE))
        {
          $this->raven->captureMessage($message, array(), $priority);
        }
      }

      if (in_array($priority, array(self::ERROR, self::EXCEPTION, self::CRITICAL), TRUE))
      {
        $this->sendEmail($message);
      }

      return $exceptionFile;
    }
    else
    {
      return parent::log($message, $priority);
    }
  }

  public function onFatalError($e)
  {
    if ($this->enabled)
    {
      $this->raven->captureException($e);
    }
  }
}
