<?php

namespace WordCampEntryPass\Classes;

class CLITool
{
    public function fix_attendees()
    {
        $emails = $this->getVolenteerEmails();

        $volunteers = AttendeeModel::filterBy('attendee_type', 'Volunteer');

        foreach ($volunteers as $volunteer) {
            if (!in_array($volunteer->email, $emails)) {
                AttendeeModel::update($volunteer->id, [
                    'attendee_type' => 'Attendee',
                    'counter' => $this->getCounter($volunteer->attendee_uid)
                ]);
                \WP_CLI::line('Updated Email: '.$volunteer->email);
            }
        }

        $duplicateEmails = [
            'tasniarahman17@gmail.com', // purchase 2 free tickets
            'mtpamir@gmail.com', // It's fine
            'rezwanksayeem@gmail.com', // it's fine
            'ashiksust06@gmail.com', // Given To Gadiel Myron
            'annuman97@gmail.com' // Duplicate
        ];

        foreach ($emails as $email) {

            if(in_array($email, $duplicateEmails)) {
                continue;
            }

            $attendees = AttendeeModel::filterBy('email', $email);

            if(!$attendees || !count($attendees)) {
                \WP_CLI::line('Email not found: '.$email);
                continue;
            }

            if(count($attendees) > 1) {
                \WP_CLI::line('Multiple Email found: '.$email);
                print_r($attendees);
                continue;
            }

            $attendee = $attendees[0];

            if($attendee->attendee_type != 'Volunteer') {
                AttendeeModel::update($attendee->id, [
                    'attendee_type' => 'Volunteer',
                    'counter' => 'F'
                ]);
                \WP_CLI::line('Made Volunteer: '.$email);
            }

        }
    }

    private function getVolenteerEmails()
    {
        return [
            '1337.shaon@gmail.com',
            'rakibantor@gmail.com',
            'skater.srabon@gmail.com',
            'khayrul005@gmail.com',
            'anjumarch79@gmail.com',
            'provathghosh531@gmail.com',
            'sherlock.masrur@gmail.com',
            'adil.murshed575@gmail.com',
            'shimulahmed641@gmail.com',
            'ashikur698@gmail.com',
            'hiehsanmoin@gmail.com',
            'info.srazu@gmail.com',
            'shakil@aref.in',
            'mahmood.showrav905@gmail.com',
            'me@jilkabir.com',
            'adrita7654321@gmail.com',
            'nazmun.sakib1042@gmail.com',
            'afrinrainy@gmail.com',
            'mumtahinafaguni11@gmail.com',
            'thisisyeasin@gmail.com',
            'tasniarahman17@gmail.com',
            'kausar.alm@gmail.com',
            'sabbir@wpdeveloper.com',
            'saiful.cse136@gmail.com',
            'mtpamir@gmail.com',
            'sadmansakibnadvi1971@gmail.com',
            'kollol.work@gmail.com',
            'arifujjaman876@gmail.com',
            'mashfik.aumy@gmail.com',
            'ahmed@wpdeveloper.com',
            'rezwanksayeem@gmail.com',
            'kazisofwan@gmail.com',
            'contactwithsadik@gmail.com',
            'contact.abutaher@gmail.com',
            'murshed4@gmail.com',
            'shohag4y@gmail.com',
            'saikatkr034@gmail.com',
            'shaontas@gmail.com',
            'mhnayem1988@gmail.com',
            'onlineshahalam@gmail.com',
            'mahmudurrahmanshamim@gmail.com',
            'rafat.technext@gmail.com',
            'souravkairy.se@gmail.com',
            'imashrurfahim@gmail.com',
            'ashiksust06@gmail.com',
            'rocksrdrahul@gmail.com',
            'mahfuzurrahman.dev@gmail.com',
            'naderchowdhury743@gmail.com',
            'sajunajmul@gmail.com',
            'dasnitesh780@gmail.com',
            'dhrupo@gmail.com',
            'tahmid.cep41@gmail.com',
            'hrdelwar75@gmail.com',
            'atiqur.su@gmail.com',
            'parthohore69@gmail.com',
            'annuman97@gmail.com',
            'codermehraj@gmail.com',
            'ahmed777emran@gmail.com',
            'nixondebantu@gmail.com',
            'uthfolghosh9038@gmail.com',
            'sadmanshawonkst11@gmail.com'
        ];
    }

    private function getCounter($uid)
    {
        if($uid <= 1799) {
            return 'A';
        }
        if($uid <= 1999) {
            return 'B';
        }

        if($uid <= 3299) {
            return 'C';
        }


        if($uid <= 3499) {
            return 'D';
        }

        return 'E';
    }


    public function generate_secret()
    {
        $attendees = AttendeeModel::getAll();

        $counter = 0;
        foreach ($attendees as $attendee) {
            if($attendee->secret_key) {
                continue;
            }

            // get random 6 character string
            $secret = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 6)), 0, 6);
            $secret.= '-'.$attendee->id;

            AttendeeModel::update($attendee->id, [
                'secret_key' => $secret
            ]);

            $counter++;
        }

        \WP_CLI::line(sprintf('Added Secret For: %d', $counter));

    }
}
