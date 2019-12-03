mcdruid/wapuu-the-ripper
========================

# <img src='wapuu_the_ripper.png' width='169' />

A password cracker for WordPress

Quick links: [Using](#using) | [Installing](#installing)

## Using

NAME

  wp wtr

DESCRIPTION

  Wapuu the Ripper - a tool to crack user passwords.

SYNOPSIS

  wp wtr [--role=<role>] [--<field>=<value>] [--top=<top>] [--all] [--hide] [--no-guessing]

ALIAS

  wapuu-the-ripper

  Based on https://www.drupal.org/project/drop_the_ripper

  Users can be filtered via arguments supported by:
  [WP_User_Query()][1].

  Uses a default wordlist from http://www.openwall.com/wordlists

  ---
  [1] https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/

OPTIONS

  [--role=<role>]
    Only display users with a certain role.

  [--<field>=<value>]
    Filter users by one or more arguments of WP_User_Query().

  [--top=<top>]
    Use the top x passwords from the wordlist.

  [--all]
    Use all of the passwords from the wordlist.

  [--hide]
    Do not show plaintext passwords in output.

  [--no-guessing]
    Disables built-in password guessing (e.g. username as password).

EXAMPLES

  wp wtr

  wp wtr --top=100 --roles=administrator

  wp wtr --all --role__not_in=subscriber

  wp wtr --exclude=1 --hide


## Installing

You can install this package with:

    wp package install git@github.com:mcdruid/wapuu-the-ripper.git

