<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Command;

use Stingus\JiraBundle\Exception\QuestionException;
use Stingus\JiraBundle\Exception\RuntimeException;
use Stingus\JiraBundle\Oauth\Oauth;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class GenerateCertCommand.
 * Generate a new private / public key for Jira OAuth
 *
 * @package Stingus\JiraBundle\Command
 */
class GenerateCertCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('stingus_jira:generate:cert')
            ->setDescription('Generate a private / public key to sign Jira requests')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Generate private / public keys for JIRA</info>');

        $countryQuestion = new Question('Enter the country name (eg. US): ');
        $countryQuestion
            ->setMaxAttempts(3)
            ->setAutocompleterValues($this->getCountries())
            ->setValidator(function ($value) {
                if (in_array($value, $this->getCountries(), true)) {
                    return $value;
                }

                throw new QuestionException('Invalid country code');
            });

        $stateQuestion = new Question('Enter the state or province name: ');
        $stateQuestion
            ->setMaxAttempts(3)
            ->setValidator($this->notEmptyValidator());

        $localityQuestion = new Question('Enter the locality name: ');
        $localityQuestion
            ->setMaxAttempts(3)
            ->setValidator($this->notEmptyValidator());

        $organizationQuestion = new Question('Enter the organization mame: ');
        $organizationQuestion
            ->setMaxAttempts(3)
            ->setValidator($this->notEmptyValidator());

        $organizationalUnitQuestion = new Question('Enter the organization unit name: ');
        $organizationalUnitQuestion
            ->setMaxAttempts(3)
            ->setValidator($this->notEmptyValidator());

        $commonNameQuestion = new Question('Enter the common name: ');
        $commonNameQuestion
            ->setMaxAttempts(3)
            ->setValidator($this->notEmptyValidator());

        $emailQuestion = new Question('Enter the email address: ');
        $emailQuestion
            ->setMaxAttempts(3)
            ->setValidator(
                function ($value) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return $value;
                    }

                    throw new QuestionException('Invalid email');
                }
            );

        $validityDaysQuestion = new Question('Enter the number of days for certificate validity: ');
        $validityDaysQuestion
            ->setMaxAttempts(3)
            ->setValidator(
                function ($value) {
                    if ($value > 0 && filter_var($value, FILTER_VALIDATE_INT)) {
                        return (int) $value;
                    }

                    throw new QuestionException('Invalid value, enter an integer value greater than zero');
                }
            );

        $helper = $this->getHelper('question');

        $certPath = sprintf(
            '%s%s%s',
            $this->getContainer()->getParameter('kernel.project_dir'),
            DIRECTORY_SEPARATOR,
            $this->getContainer()->getParameter('stingus_jira.cert_path')
        );
        if (!@mkdir($certPath) && !is_dir($certPath)) {
            throw new RuntimeException(sprintf('Could not create certs directory (%s)', $certPath));
        }

        $dn = [
            'countryName'            => $helper->ask($input, $output, $countryQuestion),
            'stateOrProvinceName'    => $helper->ask($input, $output, $stateQuestion),
            'localityName'           => $helper->ask($input, $output, $localityQuestion),
            'organizationName'       => $helper->ask($input, $output, $organizationQuestion),
            'organizationalUnitName' => $helper->ask($input, $output, $organizationalUnitQuestion),
            'commonName'             => $helper->ask($input, $output, $commonNameQuestion),
            'emailAddress'           => $helper->ask($input, $output, $emailQuestion),
        ];

        $validityDays = $helper->ask($input, $output, $validityDaysQuestion);
        $privateKey = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $csr = openssl_csr_new($dn, $privateKey);
        $cert = openssl_csr_sign($csr, null, $privateKey, $validityDays);
        $public = openssl_pkey_get_public($cert);
        $publicDetails = openssl_pkey_get_details($public);

        openssl_x509_export_to_file($cert, $certPath.DIRECTORY_SEPARATOR.Oauth::FILENAME_CERT, false);
        openssl_pkey_export_to_file($privateKey, $certPath.DIRECTORY_SEPARATOR.Oauth::FILENAME_PRIVATE);
        file_put_contents($certPath.DIRECTORY_SEPARATOR.Oauth::FILENAME_PUBLIC, $publicDetails['key']);

        $output->writeln(sprintf('<info>All done! Keys saved in %s</info>', $certPath));
    }

    /**
     * @return callable
     */
    private function notEmptyValidator(): callable
    {
        return function ($value) {
            if (!empty($value)) {
                return $value;
            }

            throw new QuestionException('This value cannot be empty');
        };
    }

    /**
     * @return array
     */
    private function getCountries(): array
    {
        return ['AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA', 'ZM', 'ZW'];
    }
}
