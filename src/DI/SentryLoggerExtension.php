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

namespace Salamek\RavenNette\DI;

use Salamek\RavenNette\SentryLogger;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Validators;
use Tracy\Debugger;

/**
 * SentryLoggerExtension for Nette DI
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class SentryLoggerExtension extends CompilerExtension
{
    /**
     * @param ClassType $class
     * @throws \Nette\Utils\AssertionException
     */
    public function afterCompile(ClassType $class)
    {
        $defaults = array();
        $defaults['inDebug'] = false;
        $defaults['directory'] = Debugger::$logDirectory;
        $defaults['email'] = Debugger::$email;
        $defaults['options'] = [];

        $config = $this->getConfig($defaults);

        Validators::assertField($config, 'dsn', 'string');

        $init = $class->getMethod('initialize');
        $init->addBody(
            '$sentryLogger = new ' . SentryLogger::class . '(?, ?, ?, ?, ?, ?);' .
            Debugger::class . '::$onFatalError[] = function($e) use($sentryLogger)' .
            '{' .
            '  $sentryLogger->onFatalError($e);' .
            '};' .
            Debugger::class . '::setLogger($sentryLogger);',
            array(
                $config['dsn'],
                $config['inDebug'],
                $config['directory'],
                $config['email'],
                false,
                $config['options']
            )
        );

        if (isset($config['context']['user']) && isset($config['context']['user'])) {
            $init->addBody(
                '$user = $this->getService(?);' .
                'if ($user->isLoggedIn()) { $sentryLogger->setUserContext($user->getId(), \'\', (array) $user->getIdentity()); }',
                ['user']
            );
        }
    }
}
