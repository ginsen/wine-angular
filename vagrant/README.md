# Wine-Angular

Aplicaciones necesarias para levantar el entorno de desarrollo.

* virtualBox
* vagrant
* Composer

# Puesta en marcha

Para levantar la VM

```
$ cd vagrant
$ vagrant up
```

Mientras se levanta la VM configura el /etc/hosts de tu maquina.

```
10.0.0.30      wine-angular
```

Para actualizar e instalar mantener ciertos paquetes hace falta usar composer,
puedes descargar la versión para windows/Linux/iOSx desde el site oficial [https://getcomposer.org/](https://getcomposer.org/)

Hacer un composer update

```
$ composer update
```

Una vez se ha levantado puedes acceder a [http://wine-angular/](http://wine-angular/)

Para acceder ha un visor de la base de datos acceder a [http://10.0.0.30/adminer/](http://10.0.0.30/adminer/)

* user: root
* password: root

Para acceder a tu VM ...

```
$ vagrant ssh
```

... o también por ssh clásico, el password es vagrant, esto es útil si quieres tunelizar por SSH el accedo a MySQL desde un IDE como PhpStorm.

```
$ ssh vagrant@10.0.0.30
```