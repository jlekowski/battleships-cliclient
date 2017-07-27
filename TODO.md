- replace fwrite(Cursor::$stream, "\r") with something decent

- handle multi-character inputs (fgets(STDIN) to ignore inputs during API call execution)

- tab to switch between chat, buttons, board

- should enter shoot or space?

- here and in other places - consistency for apikey, authToken, apiToken, token etc.

- events propagation - set lower levels in cliclient to stop propagation only here, not in the apiclient
    - can ResponseSubscriber methods use stopPropagation if cursor needs to appear "onComplete"

- console - think about a better way to inject dependencies for commands (DI Component?)

- miss, hit, sunk into constants? (in apiclient?)

- proper tests for VarnishTestCommand and E2ETestCommand

- improve or replace E2eException

- ApiClient::call() - manage debug option (instead of having it commented out)

- RequestConfigListener - maybe have isApiKeySet() isApiVersionSet() in ApiRequest not to overwrite manually set config

- VarnishTestCommand
  * default API url to be in config

- ApiCallCommand - remove/change timeout for the Client

- ApiClientFactory - finish, test, and use

- BattleshipsApiComponent
  * move to a separate repo as a component
  * make it pretty (ApiRequest)
  * move to client component too (and check how to register as a command in API) (E2ETestCommand)

- Something that works with json_encode() to array/stdClass/JsonSerializable types?

- 20 to constant

- On PHP 7.1:
 * make nullable - getApiResponse(): ?ApiResponse
 * make nullable - getHeader(): ?string
 * make nullable - getNewId(): ?string
 * use :void

- UpdateGameRequest::setAllowedValues() - more tests

- EventTypes constants: maybe have common Core/Config repo with these constants, similar as with the header?
- ApiResponse constants: maybe have common Core/Config repo with these constants, similar as with the header?
