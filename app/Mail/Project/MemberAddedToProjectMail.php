<?php

namespace App\Mail\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberAddedToProjectMail extends Mailable
{
    use Queueable, SerializesModels;
    public $project;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Project $project, User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Member Added To Project Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.member-added',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    public function build()
    {
        return $this->view('emails.member-added')
                    ->with([
                        'projectName' => $this->project->name,
                        'userName' => $this->user->name,
                        'createdBy' => $this->project->owner->name, // Assuming the Project model has an 'owner' relationship
                    ]);
    }
}
