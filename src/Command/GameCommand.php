<?php

namespace BattleshipsApi\CliClient\Command;

use BattleshipsApi\CliClient\Board\CursorHandler;
use BattleshipsApi\CliClient\Board\CursorInfo;
use BattleshipsApi\CliClient\Board\Writer;
use BattleshipsApi\CliClient\Event\CliClientEvents;
use BattleshipsApi\CliClient\Event\InputEvent;
use BattleshipsApi\CliClient\GameInfo;
use BattleshipsApi\CliClient\GameManager;
use BattleshipsApi\CliClient\Subscriber\CursorSubscriber;
use BattleshipsApi\CliClient\Subscriber\ResponseSubscriber;
use BattleshipsApi\CliClient\Subscriber\TerminalInputSubscriber;
use BattleshipsApi\Client\Client\ApiClient;
use BattleshipsApi\Client\Client\ApiClientFactory;
use BattleshipsApi\Client\Event\ApiClientEvents;
use BattleshipsApi\Client\Listener\RequestConfigListener;
use BattleshipsApi\Client\Request\Game\CreateGameRequest;
use BattleshipsApi\Client\Request\Game\GetGamesRequest;
use BattleshipsApi\Client\Request\Game\UpdateGameRequest;
use BattleshipsApi\Client\Request\User\CreateUserRequest;
use BattleshipsApi\Client\Response\ApiResponse;
use CLI\Erase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameCommand extends Command
{
    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('game')
            ->setDescription('Start battleships game')
            ->addArgument('game', InputArgument::OPTIONAL, 'Game ID')
            ->addOption('url', 'u', InputOption::VALUE_OPTIONAL, 'API url', 'http://battleships-api.dev.lekowski.pl')
            ->addUsage('123 -u http://battleships-api.dev.lekowski.pl')
            ->addUsage('--url http://battleships-api.vagrant')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');

        $url = $input->getOption('url');
        $gameId = $input->getArgument('game');

        $apiConfig = ['baseUri' => $url, 'version' => 1];

        $apiKeyStorageFile = __DIR__ . '/../../.apiKey';
        $apiKey = '';
        if (file_exists($apiKeyStorageFile) && is_readable($apiKeyStorageFile)) {
            $apiKey = trim(file_get_contents($apiKeyStorageFile));
            $apiConfig['key'] = $apiKey;
        }

        $apiClient = ApiClientFactory::build($apiConfig);
        $eventDispatcher = $apiClient->getDispatcher();

        $cursorInfo = new CursorInfo();
        $cursorHandler = new CursorHandler($cursorInfo);

        $writer = new Writer($cursorInfo, STDERR);
        $writer->setCursorHandler($cursorHandler);

        if ($apiKey === '') {
            $createUserResponse = $this->createUser($questionHelper, $input, $output, $apiClient);
            $apiKey = $createUserResponse->getHeader(ApiResponse::HEADER_API_KEY);
            try {
                $this->setupApiKey($apiKey, $eventDispatcher, $apiKeyStorageFile);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<comment>%s</comment>', $e->getMessage()));
                $output->writeln('<info>Next time you run the game, you need to create a new user</info>');

                $question = new ConfirmationQuestion('<question>Do you want to continue? </question>', true);
                if (!$questionHelper->ask($input, $output, $question)) {
                    return;
                }
            }
        }

        if ($gameId === null) {
            $gameId = $this->handleGettingGameId($questionHelper, $input, $output, $apiClient);
        }

        $gameInfo = new GameInfo($gameId);

        $gameManager = new GameManager($apiClient, $cursorHandler, $writer);
        $eventDispatcher->addSubscriber(new TerminalInputSubscriber($gameManager, $cursorInfo, $cursorHandler, $writer));
        $eventDispatcher->addSubscriber(new CursorSubscriber());
        $eventDispatcher->addSubscriber(new ResponseSubscriber($writer, $cursorHandler, $gameInfo));

        Erase::screen();

        stream_set_blocking(STDIN, false);
        system('stty -icanon -echo');

        $gameManager->run($gameInfo);
        $lastUpdate = microtime(true);
        $updateInterval = 2.0; // in sec
        while ($gameManager->keepRunning()) {
            $input = fgets(STDIN);
            if ($input) {
                // UP arrow key is 3-char long :/ Later maybe check if some predefined strings in the input?
//                // if got more than 1 character, handle them separately
//                $inputChars = mb_strlen($input) > 1
//                    ? preg_split('//u', $input, null, PREG_SPLIT_NO_EMPTY) // str_split for multibyte characters
//                    : [$input];
//                tempLog(print_r($inputChars, 1));
//                foreach ($inputChars as $inputChar) {
//                    $eventDispatcher->dispatch(CliClientEvents::ON_INPUT, new InputEvent($inputChar));
//                }
                $eventDispatcher->dispatch(CliClientEvents::ON_INPUT, new InputEvent($input));
            } elseif (microtime(true) - $lastUpdate > $updateInterval) {
                $gameManager->getUpdates();
                $lastUpdate = microtime(true);
            }
            usleep(500);
        }

        system('stty icanon echo');
    }

    /**
     * @param QuestionHelper $questionHelper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ApiClient $apiClient
     * @return ApiResponse
     * @throws \BattleshipsApi\Client\Exception\ApiException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    private function createUser(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        ApiClient $apiClient
    ): ApiResponse {
        $question = new Question('Please enter your name: ');
        $question->setValidator(function ($answer) {
            $answer = trim($answer);
            if ($answer === '') {
                throw new \RuntimeException('Name cannot be empty');
            }

            return $answer;
        });
        $playerName = $questionHelper->ask($input, $output, $question);

        $request = new CreateUserRequest();
        $request->setUserName($playerName);

        return $apiClient->call($request);
    }

    /**
     * @param string $apiKey
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $apiKeyStorageFile
     * @throws \Exception
     */
    private function setupApiKey(string $apiKey, EventDispatcherInterface $eventDispatcher, string $apiKeyStorageFile)
    {
        // set API key
        $preResolveListeners = $eventDispatcher->getListeners(ApiClientEvents::PRE_RESOLVE);
        foreach ($preResolveListeners as $preResolveListener) {
            if ($preResolveListener[0] instanceof RequestConfigListener) {
                $preResolveListener[0]->setApiKey($apiKey);
                break;
            }
        }

        // store API key
        // files does not exist, or cannot be created, or cannot write into it
        if (!file_exists($apiKeyStorageFile) || !touch($apiKeyStorageFile) || !is_writable($apiKeyStorageFile) || !file_put_contents($apiKeyStorageFile, $apiKey)) {
            throw new \Exception(sprintf('Could not save API key into file `%s`', $apiKeyStorageFile));
        }
    }

    /**
     * @param QuestionHelper $questionHelper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ApiClient $apiClient
     * @return int
     * @throws \BattleshipsApi\Client\Exception\ApiException
     * @throws \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    private function handleGettingGameId(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        ApiClient $apiClient
    ): int {
        $request = new GetGamesRequest();
        $request->setAvailable(true);
        $apiResponse = $apiClient->call($request);

        $availableGames = ['new'];
        foreach ($apiResponse->getJson() as $game) {
            $availableGames[$game->id] = $game->other->name;
        }

        $question = new ChoiceQuestion('<question>Do you want to join a game, or start a new one? </question>', $availableGames, 0);
        $answer = $questionHelper->ask($input, $output, $question);
        if ($answer === 'new') {
            $apiResponse = $apiClient->call(new CreateGameRequest());
            $gameId = $apiResponse->getNewId();
        } else {
            $gameId = array_search($answer, $availableGames);
            $request = new UpdateGameRequest();
            $request
                ->setGameId($gameId)
                ->setJoinGame(true);
            $apiClient->call($request);
        }

        return $gameId;
    }
}
