<?php

return [
    'exceptions' => [
        /**
         * 参数校验失败时返回的异常
         *
         * 该异常只表示数据在校验时失败，不应代表任何具体的含义
         * 应该只体现在接口层，而用户对此无感
         * 如“标题最多10个字符”，应在客户端进行校验
         * 在像旧版本软件包客户端无法校验时
         * 应返回指定的异常类型，而不应该使用错误参数异常
         */
        'invalid-parameter' => \Kamicloud\StubApi\Exceptions\InvalidParameterException::class,

        'auth-failed' => \Kamicloud\StubApi\Exceptions\AuthFailedException::class,

        'no-permission' => \Kamicloud\StubApi\Exceptions\NoPermissionException::class,

        /**
         * 服务器内部错误时返回的异常
         *
         * 程序在发生致命错误时会将异常转化为该异常，并记录原始异常到日志中
         * 该异常*** 不应该出现 ***
         */
        'server-internal-error' => \Kamicloud\StubApi\Exceptions\ServerInternalErrorException::class,

        /**
         * 维护模式的响应
         */
        'maintain-mode' => \Kamicloud\StubApi\Exceptions\MaintainModeException::class,

        /**
         * 等价于404
         */
        'api-not-found' => \Kamicloud\StubApi\Exceptions\ApiNotFoundException::class
    ],

    'values' => [
        'success-status' => 0,
        'success-message' => 'success',
    ],

    /**
     * 默认时间的传输格式为字符串
     *
     * 开启后传输格式为整型
     */
    'request-date-timestamp' => false,
    'response-date-timestamp' => false,
];
