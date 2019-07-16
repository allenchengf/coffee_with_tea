<?php

namespace App\Console\Commands;

use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\DomainGroup;
use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class CreateTestData extends Command
{
    protected $api, $authorization;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test-data {--name= : DomainName} {--count=12 : Domain Count}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Can Use Test Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->api = url('/api/v1');
        $this->userModule = env('USER_MODULE');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Domain::count()) {
            print_r('Please Reset All Data (DB & DNS Provider Data)' . "\n");
            print_r('Please Use This Command' . "\n\n");
            print_r('php artisan migrate:fresh --seed' . "\n");

            return;
        }

        $domainName = $this->option('name') ?? $this->ask('Please Input DomainName!');
        $dataCount = $this->option('count');

        print_r("Input DomainName is " . $domainName . "\n");
        print_r("Input Count is " . $dataCount . "\n\n");

        if (!$this->login()) {
            return;
        }

        print_r('Create Test Data Start' . "\n\n");

        if ($this->createBatchDomianAndCdn($domainName, $dataCount)) {
            $this->createIRoute();
            // $this->createDomainGroup($domainName);
            // $this->createDomainToGroup();

            print_r("All Success" . "\n");
        }
    }

    private function login($email = "brian@123.com", $password = "1qaz@WSX", $unique_id = "hiero7")
    {
        $key = "eu7nxsfttc";

        $response = Curl::to($this->userModule . '/login')
            ->withData(compact('email', 'password', 'unique_id', 'key'))
            ->asJson(true)
            ->post();

        if (empty($response['error'])) {
            print_r('Login Success' . "\n\n");

            $this->authorization = "bearer " . (string) $response['data']['token'];

            return true;
        }

        print_r('Login Fail' . "\n\n");

        return false;
    }

    private function createBatchDomianAndCdn($domainName = 'leo', $dataCount = 10)
    {
        print_r('Domains Batch Start' . "\n");

        $domainData = [];
        for ($i = 1; $i <= $dataCount; $i++) {
            $domain = "$domainName" . "$i";
            $cdns = [
                [
                    "name" => "Hiero7",
                    "cname" => "hiero7.$domain.com",
                ],
                [
                    "name" => "Cloudflare",
                    "cname" => "cloudflare.$domain.com",
                ],
                [
                    "name" => "CloudFront",
                    "cname" => "cloudFront.$domain.com",
                ],
            ];

            if ($i % 3 == 0) {
                array_shift($cdns);
            }

            $domainData[$i - 1]['name'] = "$domain.com";
            $domainData[$i - 1]['cdns'] = $cdns;
        }

        $response = Curl::to($this->api . '/domains/batch')
            ->withHeaders(['Authorization: ' . $this->authorization])
            ->withData(['domains' => $domainData])
            ->asJson(true)
            ->post();

        if (empty($response['error'])) {

            print_r('Domains Batch Success' . "\n\n");
            return true;

        }

        print_r('Domains Batch Error' . "\n");
        return false;
    }

    private function createIRoute()
    {
        print_r('Create Domains IRoute Start' . "\n");

        $location_network_ids = [1, 2, 4];
        $domains = Domain::all();
        foreach ($domains as $domain) {
            foreach ($location_network_ids as $location_network_id) {
                $cdn_id = Cdn::where('domain_id', $domain->id)->inRandomOrder()->pluck('id')->first();

                $response = Curl::to($this->api . "/domains/$domain->id/iRouteCDN/$location_network_id")
                    ->withHeaders(['Authorization: ' . $this->authorization])
                    ->withData(compact('cdn_id'))
                    ->asJson(true)
                    ->put();
            }
        }
        print_r('Create Domains IRoute Success' . "\n\n");

    }

    private function createDomainGroup($name = 'Leo')
    {
        print_r('Create Domains Group Start' . "\n");

        $name = "Test Group By $name";
        $domain_id = 1;
        $label = 'Test Label';

        $response = Curl::to($this->api . "/groups")
            ->withHeaders(['Authorization: ' . $this->authorization])
            ->withData(compact('name', 'domain_id', 'label'))
            ->asJson(true)
            ->post();

        print_r('Create Domains Group Success' . "\n\n");

    }

    private function createDomainToGroup()
    {
        print_r('Create Domains To Group Start' . "\n");

        $domain_group_id = DomainGroup::inRandomOrder()->pluck('id')->first();

        $domains = Domain::whereNotIn('id', [1])->get();
        $domain1 = Domain::find(1);
        $domain1Count = count($domain1->cdns);

        $groupLimit = 3;
        $count = 0;

        foreach ($domains as $domain) {
            if ($domain1Count == count($domain->cdns)) {
                $domain_id = $domain->id;
                $response = Curl::to($this->api . "/groups/$domain_group_id")
                    ->withHeaders(['Authorization: ' . $this->authorization])
                    ->withData(compact('domain_id'))
                    ->asJson(true)
                    ->post();

                empty($response['error']) ? $count++ : 0;

            }
            if ($groupLimit >= $count) {
                break;
            }
        }

        print_r('Create Domains To Group Success' . "\n\n");
    }
}
