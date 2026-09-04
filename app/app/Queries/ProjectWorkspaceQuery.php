<?php

namespace App\Queries;

use App\Models\ProductProject;

class ProjectWorkspaceQuery
{
    public function for(ProductProject $project): ProductProject
    {
        return $project->load(['owner', 'ownerDepartment', 'members.user', 'decisions', 'activities.actor', 'skus' => fn ($query) => $query->latest(), 'sources.skus', 'landingPages.skus', 'creativeAssets', 'campaignTests', 'optimizationFeedback']);
    }
}
