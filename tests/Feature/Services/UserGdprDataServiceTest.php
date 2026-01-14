<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\PersonalDataSelection\UserGdprDataService;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Tests\FeatureTestCase;

class UserGdprDataServiceTest extends FeatureTestCase
{
    private User $user;

    private PersonalDataSelection $personalDataSelection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->make();
        $this->personalDataSelection = $this->getMockBuilder(PersonalDataSelection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['add', 'addFile'])
            ->getMock();
    }

    public function test_correct_classes()
    {
        $this->personalDataSelection->expects($this->never())
            ->method('add')
            ->willReturn($this->personalDataSelection);

        $service = new UserGdprDataService();

        $service->addUserPersonalData($this->personalDataSelection, $this->user, false);
    }
}
