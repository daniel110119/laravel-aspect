![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)
![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D11.0-red.svg)

## Lugege\LaravelAspect 


它融合了 Java Spring Boot 的 AOP 概念，并广泛利用 PHP8 注解来简化开发流程。

> 该项目目前正在开发中，在生产环境中使用时请谨慎。
 ## 简易路由

路由需要在 `web.php` 或 `api.php` 中单独配置，这在开发过程中并不方便，因为需要在不同文件之间切换。

相比之下，像 Spring Boot 或 Flask 这样的框架允许使用注解配置路由，使编码过程更加流畅。因此，我封装了基于注解的路由功能。

首先，你需要在 路由文件中注册，代码如下：
```php
use Lugege\LaravelAspect\EasyRouter;

EasyRouter::register();
```

然后，你可以在控制器方法前使用路由注解：
```php
use Lugege\LaravelAspect\Attributes\Routes\GetMapping;
use Lugege\LaravelAspect\Attributes\Routes\Prefix;

#[Prefix(path: '/user')]
class UserController
{
    #[GetMapping(path: '/login')] 
    public function login() {}
}
```
## 自动装配

在 Java Spring Boot 框架中，`@Autowired` 注解用于自动注入依赖。我们可以使用 `#[Autowired]` 注解来实现相同的效果。

```php

use Lugege\LaravelPlus\Attributes\Service;

#[Service]
class UserService
{
    public function register() {}
}


use Lugege\LaravelPlus\Traits\Injectable;

class UserController
{
    use Injectable;
  
    #[Autowired]
    private UserService $userService;
    
    public function register()
    {
        $this->userService->register(); 
    }
}

```

`#[Autowired]` 注解可以用于属性。`#[Service]` 注解用于将类标记为服务，这是自动装配所必需的。

## 配置值注入

除了依赖注入之外，Laravel-Plus 还支持配置值注入功能，让你可以直接将 Laravel 配置文件中的值注入到类属性中：

```php
use Lugege\LaravelPlus\Traits\Injectable;
use Lugege\LaravelPlus\Attributes\Value;

class DatabaseService
{
    use Injectable;
    
    #[Value('database.connections.mysql.host', 'localhost')]
    private string $dbHost;
    
    #[Value('database.connections.mysql.port', 3306)]
    private int $dbPort;
    
    #[Value('app.timezone', 'UTC')]
    private string $timezone;
    
    public function connect()
    {
        // 使用注入的配置值
        dump("Connecting to {$this->dbHost}:{$this->dbPort}");
    }
}
```

`#[Value]` 注解的第一个参数是配置键名，第二个参数是可选的默认值。如果配置不存在，将使用默认值。

## Bean

长期以来，PHP 开发者习惯于使用功能强大的数组作为所有数据的载体。这不是一个优雅的做法，并且存在以下问题：

* 数组键容易拼写错误，当发现这些错误时，已经是运行时了
* 编码过程不够流畅；你总是需要暂停思考下一个键是什么
* 违反了单一职责原则，经常将所有数据放在一个巨大的数组中
* 降低了代码的可扩展性、可读性和健壮性...

因此，我引入了 Bean 的概念。Bean 是一个具有强类型属性的数据载体，让你在编码过程中获得更好的提示：

```php
use Lugege\LaravelPlus\Bean;

/**
 * @method getUsername()
 * @method setUsername()
 * @method getPassword()
 * @method setPassword()
 */
class RegisterParams extends Bean
{
    protected string $username;
    
    protected string $password;
}

new RegisterParams(['username' => 'bob', 'password' => 'passw0rd']);
```

你可以使用数组初始化 Bean，这是最常见的方法。当然，有时你也可以从一个 Bean 转换为另一个 Bean，它会过滤掉不匹配的字段：
```php
use Lugege\LaravelPlus\Bean;

$bean = new Bean();
class Bar extends Bean {  
    // 一些属性  
}
Bar::fromBean($bean)
```

你可以轻松地将 Bean 转换为数组或 JSON。默认情况下，将使用蛇形命名法。你可以使用 `usingSnakeCase` 参数关闭此功能：
```php
use Lugege\LaravelPlus\Bean;

$bean = new Bean();
$arr = $bean->toArray(usingSnakeCase: false);
$json = $bean->toJson(usingSnakeCase: true);
```

有时，你可能需要比较两个 Bean：
```php
use Lugege\LaravelPlus\Bean;
(new Bean())->equals($bean2);
```

通常，我们需要对从客户端传递的数据进行类型转换等预处理工作：
```php
use Lugege\LaravelPlus\Bean;
use Lugege\LaravelPlus\Attributes\TypeConverter;

class User extends Bean
{
    #[TypeConverter(value: BoolConverter::class)]
    protected BoolEnum $isGuest;
}

class BoolConverter
{
    public function convert(bool|string $value): BoolEnum
    {
        if ($value === 'YES' || $value === 'yes' || $value === 'y' || $value === 'Y') {
            return BoolEnum::TRUE;
        }
        if ($value === 'NO' || $value === 'no' || $value === 'N' || $value === 'n') {
            return BoolEnum::FALSE;
        }

        return $value ? BoolEnum::TRUE : BoolEnum::FALSE;
    }
}
```

你甚至可以执行 XSS 过滤。

Bean 的一个特别有用的功能是支持嵌套：
```php
use Lugege\LaravelPlus\Bean;

class User extends Bean
{
    protected Company $company;
}

class Company extends Bean
{
    protected string $name;
}
```

它甚至支持数组嵌套：
```php
use Lugege\LaravelPlus\Bean;
use Lugege\LaravelPlus\Attributes\BeanList;

/**
 * @method Company[] getCompanies()
 */
class User extends Bean
{
    /**
     * @var Company[]
     */
    #[BeanList(value: Company::class)]
    protected array $companies;
}

$user = new User(['companies' => [['name' => 'Lugege'], ['name' => 'Google']]]);
foreach ($user->getCompanies() as $company) {
    dump($company->getName());
}
```