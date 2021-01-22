/**
 * User online status presence channel monitor for behind the scenes on non Vue app pages.
 */

import { whenAvailable } from '../plugins/whenAvailable'
import Logger from '../logger'
require('../bootstrap/echo')

window.whenAvailable = whenAvailable

window.onlineMonitor = {
  listen() {
    Logger.debug('onlineMonitor.listen')
    whenAvailable(['Echo', 'currentUserId', 'Pusher'])
      .then(([Echo, userId, Pusher]) => {
        Logger.debug(`Attempting to join onlineMonitor user.status.${userId} channel`)
        try {
          window.Echo.join(`user.status.${userId}`)
            .here(users => {
              Logger.debug(`onlineMonitor | user.status.${userId}.here`, { users })
            })
        } catch (error) {
          Logger.error(error)
        }
      })
      .catch(error => Logger.error(error))
  }
}

window.onlineMonitor.listen()
