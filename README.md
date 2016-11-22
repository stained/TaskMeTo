TaskMeTo
========

Prerequisites
-------------

1. [VirtualBox](https://www.virtualbox.org/wiki/Downloads)
2. [Vagrant](http://docs.vagrantup.com/v2/installation/index.html)

Quickstart guide
----------------

1. Check out this repository to your local machine.
2. Open a terminal and `cd` into the project root (= the directory where this project's `Vagrantfile` lives).
3. Run `vagrant up` in the terminal.
4. Open your web browser and go to http://192.168.33.165 — if all is well, you'll get the TaskMeTo task list page.
5. Make changes, refresh browser.

Building the Vagrant box might take a while.

General Code Layout
-------------------
All custom code + configs

```
/app
```

Config files

```
/app/config
```

The flow + business logic controllers

```
/app/controller
```

Database models

```
/app/model
```

Entry (index.php) + css, javascript, and image files

```
/app/public
```

Util classes

```
/app/util
```

Front-end templates. Each folder roughly corresponds to the appropriate controller class (user, task, root).
See https://fatfreeframework.com/views-and-templates for more info about templates (and F3's templating language).

```
/app/view
```

Default html file that gets loaded

```
/app/view/default.htm
```

--

Vagrant and db deploy scripts

```
/deploy
```

--

Core FatFreeFramework

```
/lib
```
