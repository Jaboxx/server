<?php
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @copyright Copyright (c) 2016 Joas Schilling <coding@schilljs.com>
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Christoph Wurst <christoph@owncloud.com>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Victor Dubiniuk <dubiniuk@owncloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\Core;

use OC\DB\MissingIndexInformation;
use OC\DB\SchemaWrapper;
use OCP\AppFramework\App;
use OCP\IDBConnection;
use OCP\Util;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Application
 *
 * @package OC\Core
 */
class Application extends App {

	public function __construct() {
		parent::__construct('core');

		$container = $this->getContainer();

		$container->registerService('defaultMailAddress', function () {
			return Util::getDefaultEmailAddress('lostpassword-noreply');
		});

		$server = $container->getServer();
		$eventDispatcher = $server->getEventDispatcher();

		$eventDispatcher->addListener(IDBConnection::CHECK_MISSING_INDEXES_EVENT,
			function(GenericEvent $event) use ($container) {
				/** @var MissingIndexInformation $subject */
				$subject = $event->getSubject();

				$schema = new SchemaWrapper($container->query(IDBConnection::class));

				if ($schema->hasTable('share')) {
					$table = $schema->getTable('share');

					if (!$table->hasIndex('share_with_index')) {
						$subject->addHintForMissingSubject($table->getName(), 'share_with_index');
					}
					if (!$table->hasIndex('parent_index')) {
						$subject->addHintForMissingSubject($table->getName(), 'parent_index');
					}
				}
			}
		);
	}
}
