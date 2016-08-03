namespace Sakura
{
    export interface IChangelogChange
    {
        id: number;
        action?: IChangelogAction;
        contributor?: IChangelogContributor;
        major: boolean;
        url: string;
        message: string;
    }
}
