## 使用方法

在 `controller` 里 引用 trait `\Hogus\Response\ApiResponse`

## Example

- `$this->responseSuccess('message', 'code')` 返回正确响应
- `$this->responseData('data', 'message', 'code')` 返回数据（支持多类型：单数组、数据分页、resource）
- `$this->responseFailed('message', 'code', 'data')` 返回错误响应

- 指定数据返回键，默认`data`, 可以在控制器中使用`protected static $warp = 'items'` 覆盖
- 默认HTTP status 200，可以在调用前进行覆盖 `$this->setStatusCode(401)->responseFailed()`
