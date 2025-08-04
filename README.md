![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)
![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D11.0-red.svg)

## Daniel\LaravelAspect 


它融合了 Java Spring Boot 的 AOP 概念，并广泛利用 PHP8 注解来简化开发流程。

> 该项目目前正在开发中，在生产环境中使用时请谨慎。
 ## 简易路由

路由需要在 `web.php` 或 `api.php` 中单独配置，这在开发过程中并不方便，因为需要在不同文件之间切换。

相比之下，像 Spring Boot 或 Flask 这样的框架允许使用注解配置路由，使编码过程更加流畅。因此，我封装了基于注解的路由功能。

首先，你需要在 `api.php` 中注册，代码如下：
```php
use Daniel\LaravelAspect\EasyRouter;

EasyRouter::register();
```

然后，你可以在控制器方法前使用路由注解：
```php
use Daniel\LaravelAspect\Attributes\Routes\GetMapping;
use Daniel\LaravelAspect\Attributes\Routes\Prefix;

#[Prefix(path: '/user')]
class UserController
{
    #[GetMapping(path: '/login')] 
    public function login() {}
}
```
