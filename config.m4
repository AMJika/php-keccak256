dnl config.m4 for extension keccak
dnl Comments in this file start with the string 'dnl'.

PHP_ARG_ENABLE([keccak],
  [whether to enable keccak support],
  [AS_HELP_STRING([--enable-keccak],
    [Enable keccak support])],
  [no])

if test "$PHP_KECCAK" != "no"; then
  dnl Check for required headers
  AC_CHECK_HEADER([string.h], [], [
    AC_MSG_ERROR([string.h header not found])
  ])

  dnl Define the extension
  PHP_NEW_EXTENSION(keccak, keccak.c, $ext_shared)
fi
