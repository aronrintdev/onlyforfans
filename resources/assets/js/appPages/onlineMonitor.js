/**
 * User online status presence channel monitor for behind the scenes on non Vue app pages.
 */

import { whenAvailable } from '../plugins/whenAvailable'
import Logger from '../logger'

require('../bootstrap/echo')

Logger.debug('Attempting to join onlineMonitor user.status channel')

whenAvailable(['Echo', 'currentUserId'])
  .then(([Echo, userId]) => {
    Echo.join(`user.status.${userId}`)
      .here(users => {
        Logger.debug(`onlineMonitor | user.status.${userId}.here`, { users })
      })
  })
  .catch(error => {
    Logger.error(error)
  })
