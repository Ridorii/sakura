namespace Sakura
{
    export interface IChangelogDate
    {
        date: string;
        release?: IChangelogRelease;
        changes: IChangelogChange[];
    }
}
