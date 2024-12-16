<?php

namespace mindtwo\Appointable\Helper;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Stringable;

class IcsFile implements Stringable
{
    use Conditionable;

    /**
     * Start date and time for the event.
     */
    private string $start;

    /**
     * End date and time for the event.
     */
    private string $end;

    /**
     * Sequence of the event.
     */
    private int $sequence = 0;

    /**
     * Summary of the event.
     */
    private ?string $summary;

    /**
     * Location of the event.
     */
    private ?string $location;

    /**
     * Description of the event.
     */
    private ?string $description;

    /**
     * URL of the event.
     */
    private ?string $url;

    /**
     * Method of the event.
     */
    private bool $isCancelled = false;

    // TODO?
    // CREATED:20180331T150000
    // LAST-MODIFIED:20180331T150000

    // TODO? add organizer info?
    // ORGANIZER;CN=organizer@gmail.com:mailto:organizer@gmail.com

    // TODO? add attendee info?

    /**
     * Is the event an entire day event?
     */
    private bool $isEntireDay = false;

    public function __construct(
        private string $uid,
        private string $organizer,
        private ?string $attendee = null,
        private string $eol = PHP_EOL,
        private string $tsFormat = 'Ymd\THis\Z',
    ) {}

    /**
     * Set start date and time for the event
     *
     * @param  Carbon  $start
     */
    public function start(string|Carbon $start): self
    {
        // iCal date format: yyyymmddThhiissZ
        // PHP equiv format: Ymd\This\Z (note H since we want 24h format)
        $this->start = Carbon::parse($start)->format($this->tsFormat);

        return $this;
    }

    /**
     * Set end date and time for the event
     *
     * @param  Carbon  $end
     */
    public function end(string|Carbon $end): self
    {
        // iCal date format: yyyymmddThhiissZ
        // PHP equiv format: Ymd\THis\Z (note H since we want 24h format)
        $this->end = Carbon::parse($end)->format($this->tsFormat);

        return $this;
    }

    /**
     * Set method for the event.
     */
    public function cancel(): self
    {
        $this->isCancelled = true;
        // increment sequence to cancel the event
        $this->sequence = $this->sequence + 1;

        return $this;
    }

    protected function getMethod(): string
    {
        if ($this->isCancelled) {
            return 'CANCEL';
        }

        // If sequence is set, it's a request
        if ($this->sequence > 0) {
            return 'REQUEST';
        }

        return 'PUBLISH';
    }

    /**
     * Set sequence for the event.
     */
    public function sequence(?int $sequence): self
    {
        $this->sequence = $sequence ?? 0;

        return $this;
    }

    /**
     * Set location for the event.
     */
    public function location(string $location): self
    {
        $this->location = addslashes($location);

        return $this;
    }

    /**
     * Set description for the event.
     */
    public function description(string $description): self
    {
        $this->description = addslashes($description);
        $this->description = addcslashes($this->description, "\n");

        return $this;
    }

    /**
     * Set summary/title for the event.
     */
    public function summary(string $summary): self
    {
        $this->summary = addslashes($summary);

        return $this;
    }

    public function entireDay(): self
    {
        $this->isEntireDay = true;

        return $this;
    }

    /**
     * Set url for the event.
     */
    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the content of the ics file.
     */
    public function content(): string
    {
        return Str::of($this->contentStart())->trim()->append($this->eol)
            ->append($this->contentEvent())->trim()->append($this->eol)
            ->append($this->contentEnd())->trim()->append($this->eol)
            ->toString();
    }

    /**
     * Get teh VEVENT section of the ics file.
     */
    private function contentEvent(): string
    {
        $content = Str::of('BEGIN:VEVENT'.$this->eol)
            ->append('DTSTAMP:'.Carbon::now()->format($this->tsFormat).$this->eol)
            ->append('ORGANIZER:mailto:'.$this->organizer.$this->eol)
            ->append('UID:'.$this->uid.$this->eol)
            ->append('SEQUENCE:'.$this->sequence.$this->eol)
            ->when(
                $this->attendee,
                fn ($string) => $string->append($this->getAttendee().$this->eol)
            );

        // If the event is cancelled, add a comment start and return
        if ($this->isCancelled) {
            return $content
                ->append('DTSTART:'.$this->start.$this->eol)
                ->append('COMMENT:Training canceled'.$this->eol)
                ->append('END:VEVENT'.$this->eol)
                ->toString();
        }

        // All day events have a different format
        if ($this->isEntireDay) {
            $date = explode('T', $this->start)[0];

            $content = $content
                ->append('DTSTART;VALUE=DATE:'.$date.$this->eol)
                ->append('TRANSP:TRANSPARENT'.$this->eol);
        } else {
            $content = $content->append('DTSTART:'.$this->start.$this->eol)
                ->append('DTEND:'.$this->end.$this->eol);

        }

        return $content
            ->when(isset($this->summary), fn ($string) => $string->append("SUMMARY:{$this->summary}{$this->eol}"))
            ->when(! empty($this->location), fn ($string) => $string->append("LOCATION:{$this->location}{$this->eol}"))
            ->when(isset($this->description), fn ($string) => $string->append("DESCRIPTION:{$this->description}{$this->eol}"))
            ->when(isset($this->url), fn ($string) => $string->append("URL;VALUE=URI:{$this->url}{$this->eol}"))
            ->append('END:VEVENT'.$this->eol)
            ->toString();
    }

    /**
     * Get the PRODID line for the ics file.
     */
    private function getProdIdLine(): string
    {
        $appOrigin = str_replace(['http://', 'https://'], '', config('app.url'));
        $name = trim(strtolower(config('app.name')), '/');

        return "PRODID:-//$appOrigin/$name//DE";
    }

    /**
     * Get the ATTENDEE line for the ics file.
     */
    protected function getAttendee(): string
    {
        return "ATTENDEE:CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;X-NUM-GUESTS=0:mailto:{$this->attendee}";
    }

    /**
     * Returns the start of the ics file.
     */
    private function contentStart(): string
    {

        return Str::of('BEGIN:VCALENDAR'.$this->eol)
            ->append('VERSION:2.0'.$this->eol)
            ->append($this->getProdIdLine().$this->eol)
            ->append('CALSCALE:GREGORIAN'.$this->eol)
            ->append('METHOD:'.$this->getMethod().$this->eol)
            ->toString();
    }

    /**
     * Returns the end of the ics file.
     */
    private function contentEnd(): string
    {
        return 'END:VCALENDAR';
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->content();
    }
}
