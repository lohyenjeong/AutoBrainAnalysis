#!/usr/bin/python
import ansible.inventory
import ansible.playbook
import ansible.runner
import ansible.constants
from ansible import utils
from ansible import callbacks


def run_playbook(**kwargs):

    stats = callbacks.AggregateStats()
    playbook_cb = callbacks.PlaybookCallbacks(verbose=utils.VERBOSITY)
    runner_cb = callbacks.PlaybookRunnerCallbacks(
        stats, verbose=utils.VERBOSITY)
    
    # use /tmp instead of $HOME
    ansible.constants.DEFAULT_REMOTE_TMP = '/tmp/ansible'

    out = ansible.playbook.PlayBook(
        callbacks=playbook_cb,
        runner_callbacks=runner_cb,
        stats=stats,
        **kwargs
    ).run()

    return out


def handler(event, context):
    return main()


def main():
    out = run_playbook(
        playbook='process.yml',
        inventory=ansible.inventory.Inventory(['localhost'])

    )
    return(out)


if __name__ == '__main__':
    main()
